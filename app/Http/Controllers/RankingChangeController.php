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

            // 使用 Eloquent 查询构建器，保持模型关系
            $query = RankingChange::with('websiteIntroduction')
                ->whereDate('record_date', $today);

            // 筛选逻辑
            // 筛选逻辑
            if ($filterField && $filterValue !== null && $filterValue !== '') {
                // 数值筛选字段
                $numericFilterFields = [
                    'current_ranking',
                    'daily_change',
                    'week_change',
                    'biweek_change',
                    'triweek_change',
                    'month_change',
                    'quarter_change'
                ];
                
                if (in_array($filterField, $numericFilterFields)) {
                    $filterValue = (int)$filterValue;
                    
                    if ($filterField === 'current_ranking') {
                        // 使用索引友好的方式
                        $query->where('current_ranking', '<=', $filterValue);
                    } else {
                        // 分别处理正负值，避免使用 ABS 函数
                        if ($filterValue > 0) {
                            $query->where(function($q) use ($filterField, $filterValue) {
                                $q->whereBetween($filterField, [-999999, -$filterValue])
                                ->orWhereBetween($filterField, [$filterValue, 999999]);
                            });
                        }
                    }
                } 
                // 注册日期筛选
                elseif ($filterField === 'registered_after') {
                    // 验证日期格式
                    try {
                        $filterDate = \Carbon\Carbon::parse($filterValue)->format('Y-m-d');
                        
                        // 需要 join website_introductions 表
                        $query->join('website_introductions', 'ranking_changes.domain', '=', 'website_introductions.domain')
                            ->where('website_introductions.registered_at', '>', $filterDate)
                            ->select('ranking_changes.*'); // 确保只选择 ranking_changes 的字段
                            
                    } catch (\Exception $e) {
                        // 日期格式不正确时忽略此筛选
                        \Log::warning('Invalid date format in filter: ' . $filterValue);
                    }
                }
            }

            // 排序逻辑
            if ($sortBy === 'domain') {
                $query->orderBy('domain', $sortOrder);
            } else if ($sortBy === 'current_ranking') {
                // 直接使用索引
                $query->orderBy('current_ranking', $sortOrder);
            } else if ($sortBy === 'registered_at') {
                // 关联排序 - 需要 join
                $query->leftJoin('website_introductions', 'ranking_changes.domain', '=', 'website_introductions.domain')
                    ->select('ranking_changes.*');
                
                // 使用 COALESCE 处理 NULL 值
                $nullValue = $sortOrder === 'asc' ? '9999-12-31' : '1000-01-01';
                $query->orderByRaw(
                    "COALESCE(website_introductions.registered_at, ?) $sortOrder",
                    [$nullValue]
                );
            } else {
                // 变化字段排序优化
                if ($sortOrder === 'desc') {
                    // 上升最多的优先（负数）
                    $query->orderByRaw("CASE WHEN $sortBy < 0 THEN 0 ELSE 1 END, $sortBy ASC");
                } else {
                    // 下降最多的优先（正数）
                    $query->orderByRaw("CASE WHEN $sortBy > 0 THEN 0 ELSE 1 END, $sortBy DESC");
                }
            }

            // 分页
            $perPage = 100;
            $rankingChanges = $query->paginate($perPage);
            
            // 保持查询字符串
            $rankingChanges->withQueryString();

            // 缓存今日记录总数
            $cacheKey = "ranking_count_{$today}";
            $todayCount = Cache::remember($cacheKey, 300, function() use ($today) {
                return RankingChange::whereDate('record_date', $today)->count();
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