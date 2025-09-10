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
            // 重要：按 registered_at 排序或筛选都必须 JOIN
            $needsJoin = ($sortBy === 'registered_at' || 
                         $filterField === 'registered_after');

            if ($needsJoin) {
                // 使用 JOIN 查询（支持 registered_at 排序和筛选）
                $rankingChanges = $this->getJoinQuery(
                    $today, 
                    $sortBy, 
                    $sortOrder, 
                    $filterField, 
                    $filterValue
                );
            } else {
                // 使用简单查询（不需要 JOIN）
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
     * JOIN 查询方法 - 用于需要 registered_at 的场景
     */
    private function getJoinQuery($today, $sortBy, $sortOrder, $filterField, $filterValue)
    {
        $perPage = 100;
        
        // 构建 JOIN 查询
        $query = DB::table('ranking_changes as rc')
            ->leftJoin('website_introductions as wi', 'rc.domain', '=', 'wi.domain')
            ->select(
                'rc.*',  // 选择所有 ranking_changes 字段
                'wi.registered_at',
                'wi.title',
                'wi.description'
            )
            ->where('rc.record_date', $today);

        // 应用筛选条件
        if ($filterField && $filterValue !== null && $filterValue !== '') {
            if ($filterField === 'registered_after') {
                // 注册日期筛选 - 直接在 JOIN 后的结果上筛选
                $query->whereDate('wi.registered_at', '>=', $filterValue);
            } elseif ($filterField === 'current_ranking') {
                $filterValue = (int)$filterValue;
                $query->where('rc.current_ranking', '<=', $filterValue);
            } elseif (in_array($filterField, ['daily_change', 'week_change', 'biweek_change', 'triweek_change', 'month_change', 'quarter_change'])) {
                $filterValue = (int)$filterValue;
                if ($filterValue > 0) {
                    $query->where(function($q) use ($filterField, $filterValue) {
                        $q->whereBetween("rc.{$filterField}", [-999999, -$filterValue])
                          ->orWhereBetween("rc.{$filterField}", [$filterValue, 999999]);
                    });
                }
            }
        }

        // 应用排序
        if ($sortBy === 'registered_at') {
            // 按注册日期排序，NULL 值放到最后
            if ($sortOrder === 'asc') {
                $query->orderByRaw('wi.registered_at IS NULL, wi.registered_at ASC');
            } else {
                $query->orderByRaw('wi.registered_at IS NULL, wi.registered_at DESC');
            }
        } elseif ($sortBy === 'current_ranking') {
            $query->orderBy('rc.current_ranking', $sortOrder);
        } elseif ($sortBy === 'domain') {
            $query->orderBy('rc.domain', $sortOrder);
        } elseif (in_array($sortBy, ['daily_change', 'week_change', 'biweek_change', 'triweek_change', 'month_change', 'quarter_change'])) {
            // 变化字段排序
            if ($sortOrder === 'desc') {
                // 上升最多的优先（负数）
                $query->orderByRaw("CASE WHEN rc.{$sortBy} < 0 THEN 0 ELSE 1 END, rc.{$sortBy} ASC");
            } else {
                // 下降最多的优先（正数）
                $query->orderByRaw("CASE WHEN rc.{$sortBy} > 0 THEN 0 ELSE 1 END, rc.{$sortBy} DESC");
            }
        }

        // 执行分页查询
        $paginator = $query->paginate($perPage);

        // 将结果转换为 Eloquent 模型
        $items = collect($paginator->items())->map(function ($item) {
            $rankingChange = new RankingChange();
            
            // 填充所有属性
            foreach (get_object_vars($item) as $key => $value) {
                if (property_exists($rankingChange, $key)) {
                    $rankingChange->{$key} = $value;
                }
            }
            
            // 设置关联（如果有数据）
            if ($item->registered_at !== null || $item->title !== null) {
                $websiteIntro = new \App\Models\WebsiteIntroduction();
                $websiteIntro->domain = $item->domain;
                $websiteIntro->registered_at = $item->registered_at;
                $websiteIntro->title = $item->title;
                $websiteIntro->description = $item->description;
                
                $rankingChange->setRelation('websiteIntroduction', $websiteIntro);
            }
            
            return $rankingChange;
        });

        // 创建新的分页器
        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $paginator->total(),
            $paginator->perPage(),
            $paginator->currentPage(),
            ['path' => request()->url()]
        );

        $paginator->withQueryString();

        return $paginator;
    }

    /**
     * 简单查询方法 - 不需要 JOIN 的场景
     */
    private function getSimpleQuery($today, $sortBy, $sortOrder, $filterField, $filterValue)
    {
        $perPage = 100;

        // 使用 Eloquent 查询
        $query = RankingChange::where('record_date', $today);

        // 应用筛选条件（不包括 registered_after，因为那需要 JOIN）
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

        // 应用排序（不包括 registered_at，因为那需要 JOIN）
        if ($sortBy === 'current_ranking') {
            $query->orderBy('current_ranking', $sortOrder);
        } elseif ($sortBy === 'domain') {
            $query->orderBy('domain', $sortOrder);
        } elseif (in_array($sortBy, ['daily_change', 'week_change', 'biweek_change', 'triweek_change', 'month_change', 'quarter_change'])) {
            // 变化字段排序
            if ($sortOrder === 'desc') {
                // 上升最多的优先（负数）
                $query->orderByRaw("CASE WHEN {$sortBy} < 0 THEN 0 ELSE 1 END, {$sortBy} ASC");
            } else {
                // 下降最多的优先（正数）
                $query->orderByRaw("CASE WHEN {$sortBy} > 0 THEN 0 ELSE 1 END, {$sortBy} DESC");
            }
        }

        // 分页并延迟加载关联
        $paginator = $query->paginate($perPage);
        
        // 加载 websiteIntroduction 关联（用于显示，但不用于排序）
        $paginator->load('websiteIntroduction');

        $paginator->withQueryString();

        return $paginator;
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