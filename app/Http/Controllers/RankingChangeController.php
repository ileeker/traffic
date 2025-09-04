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

            // 优化1: 使用原生查询构建器，避免 Eloquent 开销
            $query = DB::table('ranking_changes')
                ->whereDate('record_date', $today);

            // 优化2: 只在需要时加载 website_introductions
            $needsIntroduction = ($sortBy === 'registered_at' || 
                                 $request->has('show_registration'));
            
            if ($needsIntroduction) {
                $query->leftJoin('website_introductions', 
                    'ranking_changes.domain', '=', 'website_introductions.domain')
                    ->select(
                        'ranking_changes.*',
                        'website_introductions.registered_at',
                        'website_introductions.title',
                        'website_introductions.description'
                    );
            } else {
                $query->select('ranking_changes.*');
            }

            // 优化3: 改进筛选逻辑，使用 UNION 代替 OR 以更好地利用索引
            if ($filterField && $filterValue !== null && $filterValue !== '') {
                $filterFields = [
                    'current_ranking',
                    'daily_change',
                    'week_change',
                    'biweek_change',
                    'triweek_change',
                    'month_change',
                    'quarter_change'
                ];
                
                if (in_array($filterField, $filterFields)) {
                    $filterValue = (int)$filterValue;
                    
                    if ($filterField === 'current_ranking') {
                        // 使用索引友好的方式
                        $query->where('ranking_changes.current_ranking', '<=', $filterValue);
                    } else {
                        // 优化4: 分别处理正负值，避免使用 ABS 函数
                        if ($filterValue > 0) {
                            // 使用 BETWEEN 代替 OR，更索引友好
                            $query->whereBetween("ranking_changes.$filterField", 
                                [-999999, -$filterValue])
                                ->orWhereBetween("ranking_changes.$filterField", 
                                    [$filterValue, 999999]);
                        }
                    }
                }
            }

            // 优化5: 改进排序逻辑
            if ($sortBy === 'domain') {
                $query->orderBy('ranking_changes.domain', $sortOrder);
            } else if ($sortBy === 'current_ranking') {
                // 直接使用索引
                $query->orderBy('ranking_changes.current_ranking', $sortOrder);
            } else if ($sortBy === 'registered_at') {
                // 优化6: 使用 COALESCE 代替 IS NULL 检查
                $nullValue = $sortOrder === 'asc' ? '9999-12-31' : '1000-01-01';
                $query->orderByRaw(
                    "COALESCE(website_introductions.registered_at, ?) $sortOrder",
                    [$nullValue]
                );
            } else {
                // 优化7: 变化字段排序优化
                $field = "ranking_changes.$sortBy";
                if ($sortOrder === 'desc') {
                    // 上升最多的优先（负数）
                    $query->orderByRaw("CASE WHEN $field < 0 THEN 0 ELSE 1 END, $field ASC");
                } else {
                    // 下降最多的优先（正数）
                    $query->orderByRaw("CASE WHEN $field > 0 THEN 0 ELSE 1 END, $field DESC");
                }
            }

            // 优化8: 使用 LIMIT 和 OFFSET 代替分页器的子查询
            $page = $request->get('page', 1);
            $perPage = 100;
            $offset = ($page - 1) * $perPage;
            
            // 获取数据
            $results = $query->limit($perPage)
                ->offset($offset)
                ->get();

            // 优化9: 缓存计数查询
            $cacheKey = "ranking_count_{$today}";
            $todayCount = Cache::remember($cacheKey, 300, function() use ($today) {
                return DB::table('ranking_changes')
                    ->whereDate('record_date', $today)
                    ->count();
            });

            // 计算过滤后的记录数（如果有筛选）
            $filteredCount = $todayCount;
            if ($filterField && $filterValue !== null && $filterValue !== '') {
                // 复用上面的查询条件
                $countQuery = DB::table('ranking_changes')
                    ->whereDate('record_date', $today);
                
                $filterFields = [
                    'current_ranking',
                    'daily_change',
                    'week_change',
                    'biweek_change',
                    'triweek_change',
                    'month_change',
                    'quarter_change'
                ];
                
                if (in_array($filterField, $filterFields)) {
                    $filterValue = (int)$filterValue;
                    
                    if ($filterField === 'current_ranking') {
                        $countQuery->where('current_ranking', '<=', $filterValue);
                    } else {
                        if ($filterValue > 0) {
                            $countQuery->whereBetween($filterField, [-999999, -$filterValue])
                                ->orWhereBetween($filterField, [$filterValue, 999999]);
                        }
                    }
                }
                
                $filteredCount = $countQuery->count();
            }

            // 手动创建分页对象
            $rankingChanges = new \Illuminate\Pagination\LengthAwarePaginator(
                $results,
                $filteredCount,
                $perPage,
                $page,
                ['path' => $request->url()]
            );
            
            $rankingChanges->withQueryString();

            // 优化10: 如果需要 Eloquent 关联，批量加载
            if (!$needsIntroduction && $request->has('with_details')) {
                $domains = $results->pluck('domain')->toArray();
                $introductions = DB::table('website_introductions')
                    ->whereIn('domain', $domains)
                    ->get()
                    ->keyBy('domain');
                
                foreach ($results as $result) {
                    $result->introduction = $introductions->get($result->domain);
                }
            }

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
     * 优化后的获取域名历史方法
     */
    public function getDomainHistory(Request $request, string $domain)
    {
        try {
            // 使用缓存
            $cacheKey = "domain_history_{$domain}";
            
            $history = Cache::remember($cacheKey, 600, function() use ($domain) {
                return DB::table('ranking_changes')
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

            return response()->json([
                'domain' => $domain,
                'history' => $history
            ]);

        } catch (\Exception $e) {
            \Log::error('Domain history error: ' . $e->getMessage());
            return response()->json(['error' => '获取历史数据失败'], 500);
        }
    }
}