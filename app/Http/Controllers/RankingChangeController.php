<?php

namespace App\Http\Controllers;

use App\Models\RankingChange;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class RankingChangeController extends Controller
{
    /**
     * 展示排名变化数据列表
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $today = now()->format('Y-m-d');

        try {
            // 获取查询参数
            $sortBy = $request->get('sort', 'current_ranking');
            $sortOrder = $request->get('order', 'asc');
            $filterField = $request->get('filter_field');
            $filterValue = $request->get('filter_value');
            
            // 验证排序字段
            $allowedSorts = [
                'domain',
                'current_ranking',
                'daily_change',
                'week_change',
                'biweek_change',
                'triweek_change',
                'month_change',
                'quarter_change',
                'registered_at'
            ];
            
            if (!in_array($sortBy, $allowedSorts)) {
                $sortBy = 'current_ranking';
            }
            
            // 验证排序顺序
            if (!in_array($sortOrder, ['asc', 'desc'])) {
                $sortOrder = 'asc';
            }

            // 判断是否需要 JOIN website_introductions 表
            $needsJoin = ($sortBy === 'registered_at' || 
                         $filterField === 'registered_after' ||
                         $request->has('with_introduction'));

            if ($needsJoin) {
                // 使用优化的 JOIN 查询（利用复合索引）
                $rankingChanges = $this->getOptimizedJoinQuery(
                    $today, 
                    $sortBy, 
                    $sortOrder, 
                    $filterField, 
                    $filterValue
                );
            } else {
                // 不需要 JOIN 的简单查询
                $rankingChanges = $this->getOptimizedSimpleQuery(
                    $today, 
                    $sortBy, 
                    $sortOrder, 
                    $filterField, 
                    $filterValue
                );
            }

            // 缓存今日记录总数
            $cacheKey = "ranking_count_{$today}";
            $todayCount = Cache::remember($cacheKey, 300, function() use ($today) {
                return DB::table('ranking_changes')
                    ->where('record_date', $today)
                    ->count();
            });

            // 计算过滤后的记录数
            $filteredCount = $rankingChanges->total();

            return view('ranking-changes.index', compact(
                'rankingChanges',
                'sortBy',
                'sortOrder',
                'filterField',
                'filterValue',
                'todayCount',
                'filteredCount'
            ));

        } catch (\Exception $e) {
            \Log::error('RankingChange index error: ' . $e->getMessage());
            return redirect()->back()->with('error', '数据加载失败：' . $e->getMessage());
        }
    }

    /**
     * 优化的 JOIN 查询方法 - 充分利用复合索引
     */
    private function getOptimizedJoinQuery($today, $sortBy, $sortOrder, $filterField, $filterValue)
    {
        $perPage = 100;
        $page = request()->get('page', 1);
        $offset = ($page - 1) * $perPage;
        
        // 根据不同的排序和筛选条件选择最优索引策略
        $indexStrategy = $this->determineIndexStrategy($sortBy, $filterField);
        
        // 构建基础查询
        $baseQuery = $this->buildOptimizedBaseQuery($indexStrategy, $today);
        
        $bindings = [$today];
        $conditions = [];
        
        // 构建筛选条件
        if ($filterField && $filterValue !== null && $filterValue !== '') {
            $filterCondition = $this->buildFilterCondition($filterField, $filterValue, $indexStrategy);
            if ($filterCondition) {
                $conditions[] = $filterCondition['sql'];
                $bindings = array_merge($bindings, $filterCondition['bindings']);
            }
        }
        
        // 添加筛选条件到查询
        if (!empty($conditions)) {
            $baseQuery .= " AND " . implode(" AND ", $conditions);
        }
        
        // 构建优化的排序子句
        $orderClause = $this->buildOptimizedOrderClause($sortBy, $sortOrder, $indexStrategy);
        $baseQuery .= " " . $orderClause;
        
        // 获取总数（使用优化的计数查询）
        $total = $this->getOptimizedCount($today, $filterField, $filterValue, $indexStrategy);
        
        // 添加分页
        $baseQuery .= " LIMIT ? OFFSET ?";
        $bindings[] = $perPage;
        $bindings[] = $offset;
        
        // 执行查询
        $results = DB::select($baseQuery, $bindings);
        
        // 转换为 Eloquent 模型集合
        $items = collect($results)->map(function ($item) {
            return $this->hydrateModel($item);
        });

        // 创建分页器
        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            ['path' => request()->url()]
        );

        return $paginator->withQueryString();
    }

    /**
     * 优化的简单查询（不需要 JOIN）
     */
    private function getOptimizedSimpleQuery($today, $sortBy, $sortOrder, $filterField, $filterValue)
    {
        $perPage = 100;

        // 根据排序字段选择最优索引
        $query = $this->selectOptimalIndex($today, $sortBy, $filterField);

        // 应用筛选条件
        if ($filterField && $filterValue !== null && $filterValue !== '') {
            if ($filterField === 'current_ranking') {
                $filterValue = (int)$filterValue;
                $query->where('current_ranking', '<=', $filterValue);
            } elseif (in_array($filterField, ['daily_change', 'week_change', 'biweek_change', 'triweek_change', 'month_change', 'quarter_change'])) {
                $filterValue = (int)$filterValue;
                if ($filterValue > 0) {
                    $query->where(function($q) use ($filterField, $filterValue) {
                        $q->whereBetween($filterField, [-999999, -$filterValue])
                          ->orWhereBetween($filterField, [$filterValue, 999999]);
                    });
                }
            }
        }

        // 应用优化的排序
        $this->applyOptimizedSort($query, $sortBy, $sortOrder);

        // 分页
        $paginator = $query->paginate($perPage);
        $paginator->withQueryString();

        return $paginator;
    }

    /**
     * 根据查询条件确定最优索引策略
     */
    private function determineIndexStrategy($sortBy, $filterField)
    {
        // 基于现有索引选择最优策略
        if ($sortBy === 'current_ranking' || ($filterField === 'current_ranking' && !$sortBy)) {
            return 'idx_date_ranking_optimized'; // 使用 idx_date_ranking_optimized
        }
        
        if ($sortBy === 'daily_change' || $filterField === 'daily_change') {
            return 'idx_date_daily'; // 使用 idx_date_daily
        }
        
        if ($sortBy === 'week_change' || $filterField === 'week_change') {
            return 'idx_date_week'; // 使用 idx_date_week
        }
        
        if ($sortBy === 'biweek_change' || $filterField === 'biweek_change') {
            return 'idx_date_biweek'; // 使用 idx_date_biweek
        }
        
        if ($sortBy === 'triweek_change' || $filterField === 'triweek_change') {
            return 'idx_date_triweek'; // 使用 idx_date_triweek
        }
        
        if ($sortBy === 'month_change' || $filterField === 'month_change') {
            return 'idx_date_month'; // 使用 idx_date_month
        }
        
        if ($sortBy === 'quarter_change' || $filterField === 'quarter_change') {
            return 'idx_date_quarter'; // 使用 idx_date_quarter
        }
        
        if ($sortBy === 'registered_at' || $filterField === 'registered_after') {
            return 'idx_registered_join'; // 需要优化 JOIN 查询
        }
        
        if ($sortBy === 'domain') {
            return 'idx_domain_date'; // 使用 idx_domain_date
        }
        
        return 'idx_date_ranking_optimized'; // 默认使用最通用的索引
    }

    /**
     * 构建优化的基础查询（根据索引策略）
     */
    private function buildOptimizedBaseQuery($indexStrategy, $today)
    {
        // 根据不同的索引策略构建查询
        switch ($indexStrategy) {
            case 'idx_registered_join':
                // 对于 registered_at 相关查询，优化 JOIN 顺序
                return "
                    SELECT 
                        rc.id,
                        rc.domain,
                        rc.current_ranking,
                        rc.daily_change,
                        rc.daily_trend,
                        rc.week_change,
                        rc.week_trend,
                        rc.biweek_change,
                        rc.biweek_trend,
                        rc.triweek_change,
                        rc.triweek_trend,
                        rc.month_change,
                        rc.month_trend,
                        rc.quarter_change,
                        rc.quarter_trend,
                        rc.record_date,
                        wi.registered_at
                    FROM ranking_changes rc
                    FORCE INDEX (idx_date_ranking_optimized)
                    STRAIGHT_JOIN website_introductions wi 
                        FORCE INDEX (idx_domain_registered)
                        ON rc.domain = wi.domain
                    WHERE rc.record_date = ?
                ";
                
            case 'idx_date_daily':
            case 'idx_date_week':
            case 'idx_date_biweek':
            case 'idx_date_triweek':
            case 'idx_date_month':
            case 'idx_date_quarter':
                // 对于变化值相关查询，使用对应的专门索引
                $indexName = $indexStrategy;
                return "
                    SELECT 
                        rc.id,
                        rc.domain,
                        rc.current_ranking,
                        rc.daily_change,
                        rc.daily_trend,
                        rc.week_change,
                        rc.week_trend,
                        rc.biweek_change,
                        rc.biweek_trend,
                        rc.triweek_change,
                        rc.triweek_trend,
                        rc.month_change,
                        rc.month_trend,
                        rc.quarter_change,
                        rc.quarter_trend,
                        rc.record_date,
                        wi.registered_at
                    FROM ranking_changes rc
                    FORCE INDEX ({$indexName})
                    LEFT JOIN website_introductions wi 
                        FORCE INDEX (domain_unique)
                        ON rc.domain = wi.domain
                    WHERE rc.record_date = ?
                ";
                
            default:
                // 默认查询
                return "
                    SELECT 
                        rc.id,
                        rc.domain,
                        rc.current_ranking,
                        rc.daily_change,
                        rc.daily_trend,
                        rc.week_change,
                        rc.week_trend,
                        rc.biweek_change,
                        rc.biweek_trend,
                        rc.triweek_change,
                        rc.triweek_trend,
                        rc.month_change,
                        rc.month_trend,
                        rc.quarter_change,
                        rc.quarter_trend,
                        rc.record_date,
                        wi.registered_at
                    FROM ranking_changes rc
                    FORCE INDEX (idx_date_ranking_optimized)
                    LEFT JOIN website_introductions wi 
                        FORCE INDEX (domain_unique)
                        ON rc.domain = wi.domain
                    WHERE rc.record_date = ?
                ";
        }
    }

    /**
     * 构建筛选条件
     */
    private function buildFilterCondition($filterField, $filterValue, $indexStrategy)
    {
        $condition = ['sql' => '', 'bindings' => []];
        
        if ($filterField === 'registered_after') {
            $condition['sql'] = "wi.registered_at >= ?";
            $condition['bindings'][] = $filterValue;
        } elseif ($filterField === 'current_ranking') {
            $filterValue = (int)$filterValue;
            $condition['sql'] = "rc.current_ranking <= ?";
            $condition['bindings'][] = $filterValue;
        } elseif (in_array($filterField, ['daily_change', 'week_change', 'biweek_change', 'triweek_change', 'month_change', 'quarter_change'])) {
            $filterValue = (int)$filterValue;
            if ($filterValue > 0) {
                $condition['sql'] = "(rc.{$filterField} BETWEEN ? AND ? OR rc.{$filterField} BETWEEN ? AND ?)";
                $condition['bindings'] = [-999999, -$filterValue, $filterValue, 999999];
            }
        }
        
        return $condition;
    }

    /**
     * 构建优化的排序子句
     */
    private function buildOptimizedOrderClause($sortBy, $sortOrder, $indexStrategy)
    {
        $orderClause = "ORDER BY ";
        
        // 如果排序字段与索引策略匹配，可以利用索引排序
        if ($this->canUseIndexForSorting($sortBy, $indexStrategy)) {
            // 索引已经提供了排序，只需指定方向
            if ($sortBy === 'registered_at') {
                $nullValue = $sortOrder === 'asc' ? '9999-12-31' : '1000-01-01';
                $orderClause .= "COALESCE(wi.registered_at, '{$nullValue}') {$sortOrder}";
            } elseif (in_array($sortBy, ['daily_change', 'week_change', 'biweek_change', 'triweek_change', 'month_change', 'quarter_change'])) {
                if ($sortOrder === 'desc') {
                    $orderClause .= "CASE WHEN rc.{$sortBy} < 0 THEN 0 ELSE 1 END, rc.{$sortBy} ASC";
                } else {
                    $orderClause .= "CASE WHEN rc.{$sortBy} > 0 THEN 0 ELSE 1 END, rc.{$sortBy} DESC";
                }
            } else {
                $orderClause .= "rc.{$sortBy} {$sortOrder}";
            }
        } else {
            // 需要额外的排序
            switch ($sortBy) {
                case 'domain':
                    $orderClause .= "rc.domain {$sortOrder}";
                    break;
                case 'current_ranking':
                    $orderClause .= "rc.current_ranking {$sortOrder}";
                    break;
                case 'registered_at':
                    $nullValue = $sortOrder === 'asc' ? '9999-12-31' : '1000-01-01';
                    $orderClause .= "COALESCE(wi.registered_at, '{$nullValue}') {$sortOrder}";
                    break;
                case 'daily_change':
                case 'week_change':
                case 'biweek_change':
                case 'triweek_change':
                case 'month_change':
                case 'quarter_change':
                    if ($sortOrder === 'desc') {
                        $orderClause .= "CASE WHEN rc.{$sortBy} < 0 THEN 0 ELSE 1 END, rc.{$sortBy} ASC";
                    } else {
                        $orderClause .= "CASE WHEN rc.{$sortBy} > 0 THEN 0 ELSE 1 END, rc.{$sortBy} DESC";
                    }
                    break;
                default:
                    $orderClause .= "rc.current_ranking ASC";
            }
        }
        
        return $orderClause;
    }

    /**
     * 判断是否可以使用索引进行排序
     */
    private function canUseIndexForSorting($sortBy, $indexStrategy)
    {
        $indexSortingMap = [
            'idx_date_ranking_optimized' => ['current_ranking'],
            'idx_date_daily' => ['daily_change'],
            'idx_date_week' => ['week_change'],
            'idx_date_biweek' => ['biweek_change'],
            'idx_date_triweek' => ['triweek_change'],
            'idx_date_month' => ['month_change'],
            'idx_date_quarter' => ['quarter_change'],
            'idx_domain_date' => ['domain'],
            'idx_registered_join' => ['registered_at']
        ];
        
        return isset($indexSortingMap[$indexStrategy]) && 
               in_array($sortBy, $indexSortingMap[$indexStrategy]);
    }

    /**
     * 选择最优索引（用于简单查询）
     */
    private function selectOptimalIndex($today, $sortBy, $filterField)
    {
        $query = RankingChange::where('record_date', $today);
        
        // 根据查询条件提示 MySQL 使用特定索引
        if ($sortBy === 'current_ranking' || $filterField === 'current_ranking') {
            // 使用 idx_date_ranking_optimized
            $query = RankingChange::from(DB::raw('ranking_changes FORCE INDEX (idx_date_ranking_optimized)'))
                ->where('record_date', $today);
        } elseif ($sortBy === 'daily_change' || $filterField === 'daily_change') {
            // 使用 idx_date_daily
            $query = RankingChange::from(DB::raw('ranking_changes FORCE INDEX (idx_date_daily)'))
                ->where('record_date', $today);
        } elseif ($sortBy === 'week_change' || $filterField === 'week_change') {
            // 使用 idx_date_week
            $query = RankingChange::from(DB::raw('ranking_changes FORCE INDEX (idx_date_week)'))
                ->where('record_date', $today);
        } elseif ($sortBy === 'domain') {
            // 使用 idx_domain_date
            $query = RankingChange::from(DB::raw('ranking_changes FORCE INDEX (idx_domain_date)'))
                ->where('record_date', $today);
        }
        
        return $query;
    }

    /**
     * 应用优化的排序
     */
    private function applyOptimizedSort($query, $sortBy, $sortOrder)
    {
        if ($sortBy === 'domain') {
            $query->orderBy('domain', $sortOrder);
        } elseif ($sortBy === 'current_ranking') {
            $query->orderBy('current_ranking', $sortOrder);
        } elseif (in_array($sortBy, ['daily_change', 'week_change', 'biweek_change', 'triweek_change', 'month_change', 'quarter_change'])) {
            // 变化字段排序优化
            if ($sortOrder === 'desc') {
                // 上升最多的优先（负数）
                $query->orderByRaw("CASE WHEN {$sortBy} < 0 THEN 0 ELSE 1 END, {$sortBy} ASC");
            } else {
                // 下降最多的优先（正数）
                $query->orderByRaw("CASE WHEN {$sortBy} > 0 THEN 0 ELSE 1 END, {$sortBy} DESC");
            }
        } else {
            $query->orderBy('current_ranking', 'asc');
        }
    }

    /**
     * 获取优化的计数查询
     */
    private function getOptimizedCount($today, $filterField, $filterValue, $indexStrategy)
    {
        // 使用缓存避免重复计数
        $cacheKey = "filtered_count_{$today}_{$filterField}_{$filterValue}_{$indexStrategy}";
        
        return Cache::remember($cacheKey, 60, function() use ($today, $filterField, $filterValue, $indexStrategy) {
            // 构建优化的计数查询
            $countQuery = "
                SELECT COUNT(*) as total
                FROM ranking_changes rc
            ";
            
            $bindings = [$today];
            
            // 只在需要时 JOIN
            if ($filterField === 'registered_after') {
                $countQuery .= " FORCE INDEX (idx_date_ranking_optimized)
                    INNER JOIN website_introductions wi 
                    FORCE INDEX (idx_registered_at_domain)
                    ON rc.domain = wi.domain";
            } else {
                // 选择合适的索引进行计数
                $indexName = $this->getCountIndexName($filterField);
                if ($indexName) {
                    $countQuery .= " FORCE INDEX ({$indexName})";
                }
            }
            
            $countQuery .= " WHERE rc.record_date = ?";
            
            // 添加筛选条件
            if ($filterField === 'registered_after' && $filterValue) {
                $countQuery .= " AND wi.registered_at >= ?";
                $bindings[] = $filterValue;
            } elseif ($filterField === 'current_ranking' && $filterValue) {
                $countQuery .= " AND rc.current_ranking <= ?";
                $bindings[] = (int)$filterValue;
            } elseif (in_array($filterField, ['daily_change', 'week_change', 'biweek_change', 'triweek_change', 'month_change', 'quarter_change']) && $filterValue) {
                $filterValue = (int)$filterValue;
                if ($filterValue > 0) {
                    $countQuery .= " AND (rc.{$filterField} BETWEEN ? AND ? OR rc.{$filterField} BETWEEN ? AND ?)";
                    $bindings[] = -999999;
                    $bindings[] = -$filterValue;
                    $bindings[] = $filterValue;
                    $bindings[] = 999999;
                }
            }
            
            $result = DB::selectOne($countQuery, $bindings);
            return $result->total;
        });
    }

    /**
     * 获取计数查询的最优索引名
     */
    private function getCountIndexName($filterField)
    {
        $indexMap = [
            'current_ranking' => 'idx_date_ranking_optimized',
            'daily_change' => 'idx_date_daily',
            'week_change' => 'idx_date_week',
            'biweek_change' => 'idx_date_biweek',
            'triweek_change' => 'idx_date_triweek',
            'month_change' => 'idx_date_month',
            'quarter_change' => 'idx_date_quarter'
        ];
        
        return $indexMap[$filterField] ?? 'idx_date_ranking_optimized';
    }

    /**
     * 将数据库结果转换为 Eloquent 模型
     */
    private function hydrateModel($data)
    {
        $rankingChange = new RankingChange();
        
        // 填充 ranking_changes 表的字段
        $rankingChange->id = $data->id;
        $rankingChange->domain = $data->domain;
        $rankingChange->current_ranking = $data->current_ranking;
        $rankingChange->daily_change = $data->daily_change;
        $rankingChange->daily_trend = $data->daily_trend;
        $rankingChange->week_change = $data->week_change;
        $rankingChange->week_trend = $data->week_trend;
        $rankingChange->biweek_change = $data->biweek_change;
        $rankingChange->biweek_trend = $data->biweek_trend;
        $rankingChange->triweek_change = $data->triweek_change;
        $rankingChange->triweek_trend = $data->triweek_trend;
        $rankingChange->month_change = $data->month_change;
        $rankingChange->month_trend = $data->month_trend;
        $rankingChange->quarter_change = $data->quarter_change;
        $rankingChange->quarter_trend = $data->quarter_trend;
        $rankingChange->record_date = $data->record_date;
        
        // 手动设置关联数据（如果存在）
        if (isset($data->registered_at) && $data->registered_at !== null) {
            $websiteIntro = new \App\Models\WebsiteIntroduction();
            $websiteIntro->domain = $data->domain;
            $websiteIntro->registered_at = $data->registered_at;
            
            $rankingChange->setRelation('websiteIntroduction', $websiteIntro);
        }
        
        return $rankingChange;
    }
    
    /**
     * 获取域名历史方法
     */
    public function getDomainHistory(Request $request, string $domain)
    {
        try {
            // 使用缓存
            $cacheKey = "domain_history_{$domain}";
            
            $history = Cache::remember($cacheKey, 600, function() use ($domain) {
                return RankingChange::from(DB::raw('ranking_changes FORCE INDEX (idx_domain_date)'))
                    ->where('domain', $domain)
                    ->orderBy('record_date', 'desc')
                    ->limit(30)
                    ->get([
                        'record_date',
                        'current_ranking',
                        'daily_change',
                        'daily_trend',
                        'week_change',
                        'week_trend',
                        'month_change',
                        'month_trend'
                    ]);
            });

            // 获取网站介绍信息
            $introduction = \App\Models\WebsiteIntroduction::where('domain', $domain)->first();

            return response()->json([
                'domain' => $domain,
                'history' => $history,
                'websiteIntroduction' => $introduction
            ]);

        } catch (\Exception $e) {
            \Log::error('Domain history error: ' . $e->getMessage());
            return response()->json(['error' => '获取历史数据失败'], 500);
        }
    }

    /**
     * 优化后的获取最大变化值方法
     */
    private function getMaxChanges(string $date): array
    {
        // 使用缓存避免重复查询
        $cacheKey = "max_changes_{$date}";
        
        return Cache::remember($cacheKey, 3600, function() use ($date) {
            $result = [];
            
            // 使用单个查询获取所有最大值
            $stats = DB::table('ranking_changes')
                ->from(DB::raw('ranking_changes FORCE INDEX (idx_date_ranking_optimized)'))
                ->where('record_date', $date)
                ->selectRaw('
                    MIN(CASE WHEN daily_change < 0 THEN daily_change END) as max_daily_up,
                    MAX(CASE WHEN daily_change > 0 THEN daily_change END) as max_daily_down,
                    MIN(CASE WHEN week_change < 0 THEN week_change END) as max_week_up,
                    MAX(CASE WHEN week_change > 0 THEN week_change END) as max_week_down,
                    MIN(CASE WHEN month_change < 0 THEN month_change END) as max_month_up,
                    MAX(CASE WHEN month_change > 0 THEN month_change END) as max_month_down,
                    MIN(CASE WHEN quarter_change < 0 THEN quarter_change END) as max_quarter_up,
                    MAX(CASE WHEN quarter_change > 0 THEN quarter_change END) as max_quarter_down
                ')
                ->first();
            
            $result['max_daily_change'] = max(
                abs($stats->max_daily_up ?? 0),
                abs($stats->max_daily_down ?? 0)
            );
            $result['max_week_change'] = max(
                abs($stats->max_week_up ?? 0),
                abs($stats->max_week_down ?? 0)
            );
            $result['max_month_change'] = max(
                abs($stats->max_month_up ?? 0),
                abs($stats->max_month_down ?? 0)
            );
            $result['max_quarter_change'] = max(
                abs($stats->max_quarter_up ?? 0),
                abs($stats->max_quarter_down ?? 0)
            );
            
            return $result;
        });
    }
}