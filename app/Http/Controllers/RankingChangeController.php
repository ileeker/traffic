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
                         $request->has('with_introduction')); // 可选参数，强制加载介绍信息

            if ($needsJoin) {
                // 使用优化的 JOIN 查询
                $rankingChanges = $this->getOptimizedJoinQuery(
                    $today, 
                    $sortBy, 
                    $sortOrder, 
                    $filterField, 
                    $filterValue
                );
            } else {
                // 不需要 JOIN 的简单查询
                $rankingChanges = $this->getSimpleQuery(
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
     * 优化的 JOIN 查询方法
     */
    private function getOptimizedJoinQuery($today, $sortBy, $sortOrder, $filterField, $filterValue)
    {
        $perPage = 100;
        
        // 构建基础查询，使用 INNER JOIN 提升性能
        // 如果确定所有 domain 都有对应的 introduction，使用 INNER JOIN
        // 否则使用 LEFT JOIN
        $query = DB::table('ranking_changes as rc')
            ->leftJoin('website_introductions as wi', 'rc.domain', '=', 'wi.domain')
            ->select(
                'rc.id',
                'rc.domain',
                'rc.current_ranking',
                'rc.daily_change',
                'rc.daily_trend',
                'rc.week_change',
                'rc.week_trend',
                'rc.biweek_change',
                'rc.biweek_trend',
                'rc.triweek_change',
                'rc.triweek_trend',
                'rc.month_change',
                'rc.month_trend',
                'rc.quarter_change',
                'rc.quarter_trend',
                'rc.record_date',
                'wi.registered_at',
                'wi.title',
                'wi.description'
            )
            ->where('rc.record_date', $today);

        // 应用筛选条件
        $this->applyFilters($query, $filterField, $filterValue, true);

        // 应用排序
        $this->applySort($query, $sortBy, $sortOrder, true);

        // 执行分页查询
        $paginator = $query->paginate($perPage);

        // 将结果转换为 Eloquent 模型集合
        $items = collect($paginator->items())->map(function ($item) {
            // 创建 RankingChange 模型实例
            $rankingChange = new RankingChange();
            
            // 填充 ranking_changes 表的字段
            $rankingChange->id = $item->id;
            $rankingChange->domain = $item->domain;
            $rankingChange->current_ranking = $item->current_ranking;
            $rankingChange->daily_change = $item->daily_change;
            $rankingChange->daily_trend = $item->daily_trend;
            $rankingChange->week_change = $item->week_change;
            $rankingChange->week_trend = $item->week_trend;
            $rankingChange->biweek_change = $item->biweek_change;
            $rankingChange->biweek_trend = $item->biweek_trend;
            $rankingChange->triweek_change = $item->triweek_change;
            $rankingChange->triweek_trend = $item->triweek_trend;
            $rankingChange->month_change = $item->month_change;
            $rankingChange->month_trend = $item->month_trend;
            $rankingChange->quarter_change = $item->quarter_change;
            $rankingChange->quarter_trend = $item->quarter_trend;
            $rankingChange->record_date = $item->record_date;
            
            // 手动设置关联数据（如果存在）
            if ($item->title !== null) {
                $websiteIntro = new \App\Models\WebsiteIntroduction();
                $websiteIntro->domain = $item->domain;
                $websiteIntro->registered_at = $item->registered_at;
                $websiteIntro->title = $item->title;
                $websiteIntro->description = $item->description;
                
                $rankingChange->setRelation('websiteIntroduction', $websiteIntro);
            }
            
            return $rankingChange;
        });

        // 手动创建分页器以保持分页功能
        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $paginator->total(),
            $paginator->perPage(),
            $paginator->currentPage(),
            ['path' => request()->url()]
        );

        // 保持查询字符串
        $paginator->withQueryString();

        return $paginator;
    }

    /**
     * 简单查询方法（不需要 JOIN）
     */
    private function getSimpleQuery($today, $sortBy, $sortOrder, $filterField, $filterValue)
    {
        $perPage = 100;

        // 使用 Eloquent 查询构建器
        $query = RankingChange::where('record_date', $today);

        // 应用筛选条件
        $this->applyFilters($query, $filterField, $filterValue, false);

        // 应用排序
        $this->applySort($query, $sortBy, $sortOrder, false);

        // 分页
        $paginator = $query->paginate($perPage);
        
        // 按需延迟加载 websiteIntroduction
        if (request()->has('load_introduction')) {
            $paginator->load('websiteIntroduction');
        }

        // 保持查询字符串
        $paginator->withQueryString();

        return $paginator;
    }

    /**
     * 应用筛选条件
     */
    private function applyFilters($query, $filterField, $filterValue, $isJoinQuery)
    {
        if (!$filterField || $filterValue === null || $filterValue === '') {
            return;
        }

        $filterFields = [
            'current_ranking',
            'daily_change',
            'week_change',
            'biweek_change',
            'triweek_change',
            'month_change',
            'quarter_change',
            'registered_after'
        ];
        
        if (!in_array($filterField, $filterFields)) {
            return;
        }

        if ($filterField === 'registered_after') {
            if ($isJoinQuery) {
                // JOIN 查询直接使用字段
                $query->whereDate('wi.registered_at', '>=', $filterValue);
            } else {
                // 非 JOIN 查询使用子查询
                $query->whereExists(function ($q) use ($filterValue) {
                    $q->select(DB::raw(1))
                        ->from('website_introductions')
                        ->whereColumn('website_introductions.domain', 'ranking_changes.domain')
                        ->whereDate('registered_at', '>=', $filterValue);
                });
            }
        } elseif ($filterField === 'current_ranking') {
            $filterValue = (int)$filterValue;
            $field = $isJoinQuery ? 'rc.current_ranking' : 'current_ranking';
            $query->where($field, '<=', $filterValue);
        } else {
            // 变化值筛选
            $filterValue = (int)$filterValue;
            $field = $isJoinQuery ? "rc.{$filterField}" : $filterField;
            
            if ($filterValue > 0) {
                $query->where(function($q) use ($field, $filterValue) {
                    $q->whereBetween($field, [-999999, -$filterValue])
                      ->orWhereBetween($field, [$filterValue, 999999]);
                });
            }
        }
    }

    /**
     * 应用排序
     */
    private function applySort($query, $sortBy, $sortOrder, $isJoinQuery)
    {
        if ($sortBy === 'domain') {
            $field = $isJoinQuery ? 'rc.domain' : 'domain';
            $query->orderBy($field, $sortOrder);
        } elseif ($sortBy === 'current_ranking') {
            $field = $isJoinQuery ? 'rc.current_ranking' : 'current_ranking';
            $query->orderBy($field, $sortOrder);
        } elseif ($sortBy === 'registered_at') {
            if ($isJoinQuery) {
                // 处理 NULL 值
                $nullValue = $sortOrder === 'asc' ? '9999-12-31' : '1000-01-01';
                $query->orderByRaw(
                    "COALESCE(wi.registered_at, ?) $sortOrder",
                    [$nullValue]
                );
            } else {
                // 非 JOIN 查询不应该按 registered_at 排序
                // 回退到默认排序
                $query->orderBy('current_ranking', 'asc');
            }
        } else {
            // 变化字段排序优化
            $field = $isJoinQuery ? "rc.{$sortBy}" : $sortBy;
            
            if ($sortOrder === 'desc') {
                // 上升最多的优先（负数）
                $query->orderByRaw("CASE WHEN {$field} < 0 THEN 0 ELSE 1 END, {$field} ASC");
            } else {
                // 下降最多的优先（正数）
                $query->orderByRaw("CASE WHEN {$field} > 0 THEN 0 ELSE 1 END, {$field} DESC");
            }
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
    
    /**
     * 获取域名历史方法
     */
    public function getDomainHistory(Request $request, string $domain)
    {
        try {
            // 使用缓存
            $cacheKey = "domain_history_{$domain}";
            
            $history = Cache::remember($cacheKey, 600, function() use ($domain) {
                return RankingChange::where('domain', $domain)
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
}