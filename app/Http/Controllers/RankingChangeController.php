<?php

namespace App\Http\Controllers;

use App\Models\RankingChange;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

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

            // 决定是否需要JOIN查询: 当按注册时间排序或过滤时都需要
            $needsJoin = ($sortBy === 'registered_at') || 
                        in_array($filterField, ['registered_after', 'registered_before']);

            // 构建查询
            if ($needsJoin) {
                // 需要注册时间排序或过滤时，使用JOIN查询
                $query = DB::table('ranking_changes')
                    ->leftJoin('website_introductions', 'ranking_changes.domain', '=', 'website_introductions.domain')
                    ->whereDate('ranking_changes.record_date', $today)
                    ->select(
                        'ranking_changes.*',
                        'website_introductions.registered_at',
                        'website_introductions.title',
                        'website_introductions.description'
                    );
            } else {
                // 不需要时，使用Eloquent查询以获得更好的性能和便利性
                $query = RankingChange::whereDate('record_date', $today);
            }

            // 应用筛选
            if ($filterField && $filterValue !== null && $filterValue !== '') {
                // 排名和变化值筛选
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
                    $fieldName = $needsJoin ? "ranking_changes.$filterField" : $filterField;
                    
                    if ($filterField === 'current_ranking') {
                        // 排名筛选：小于等于指定值
                        $query->where($fieldName, '<=', $filterValue);
                    } else {
                        // 变化值筛选：绝对值大于等于指定值
                        if ($filterValue > 0) {
                            $query->where(function($q) use ($fieldName, $filterValue) {
                                $q->whereBetween($fieldName, [-999999, -$filterValue])
                                  ->orWhereBetween($fieldName, [$filterValue, 999999]);
                            });
                        }
                    }
                } 
                // 处理注册日期过滤
                elseif (in_array($filterField, ['registered_after', 'registered_before'])) {
                    try {
                        $filterDate = Carbon::parse($filterValue)->toDateString();
                        if ($filterField === 'registered_after') {
                            $query->whereDate('website_introductions.registered_at', '>=', $filterDate);
                        } elseif ($filterField === 'registered_before') {
                            $query->whereDate('website_introductions.registered_at', '<=', $filterDate);
                        }
                    } catch (\Exception $e) {
                        // 如果日期格式错误，则忽略此过滤器
                        \Log::warning('Invalid date format for filter: ' . $filterValue);
                    }
                }
            }

            // 应用排序
            if ($sortBy === 'registered_at') {
                // 处理 NULL 值，NULL 值排在最后
                $nullValue = $sortOrder === 'asc' ? '9999-12-31' : '1000-01-01';
                $query->orderByRaw("COALESCE(website_introductions.registered_at, ?) $sortOrder", [$nullValue]);
            } elseif (in_array($sortBy, ['daily_change', 'week_change', 'biweek_change', 'triweek_change', 'month_change', 'quarter_change'])) {
                // 变化字段排序优化
                $fieldName = $needsJoin ? "ranking_changes.$sortBy" : $sortBy;
                
                if ($sortOrder === 'desc') {
                    // 上升最多的优先（负数在前）
                    $query->orderByRaw("CASE WHEN $fieldName < 0 THEN 0 ELSE 1 END, $fieldName ASC");
                } else {
                    // 下降最多的优先（正数在前）
                    $query->orderByRaw("CASE WHEN $fieldName > 0 THEN 0 ELSE 1 END, $fieldName DESC");
                }
            } else {
                // 其他字段直接排序
                $fieldName = $needsJoin ? "ranking_changes.$sortBy" : $sortBy;
                $query->orderBy($fieldName, $sortOrder);
            }

            // 分页查询
            $perPage = 100;
            if ($needsJoin) {
                // DB查询的分页
                $rankingChanges = $query->paginate($perPage);
                
                // 手动转换为 Eloquent 模型集合
                $items = collect($rankingChanges->items())->map(function ($item) {
                    // 创建 RankingChange 模型实例
                    $rankingChange = new RankingChange();
                    
                    // 填充所有字段
                    foreach (get_object_vars($item) as $key => $value) {
                        if ($key !== 'registered_at' && $key !== 'title' && $key !== 'description') {
                            $rankingChange->setAttribute($key, $value);
                        }
                    }
                    
                    // 如果有网站介绍信息，创建关联
                    if (isset($item->registered_at)) {
                        $websiteIntro = new \App\Models\WebsiteIntroduction();
                        $websiteIntro->domain = $item->domain;
                        $websiteIntro->registered_at = $item->registered_at;
                        $websiteIntro->title = $item->title;
                        $websiteIntro->description = $item->description;
                        
                        $rankingChange->setRelation('websiteIntroduction', $websiteIntro);
                    }
                    
                    return $rankingChange;
                });
                
                // 重新创建分页器以保持 Eloquent 集合的功能
                $rankingChanges = new \Illuminate\Pagination\LengthAwarePaginator(
                    $items,
                    $rankingChanges->total(),
                    $rankingChanges->perPage(),
                    $rankingChanges->currentPage(),
                    ['path' => request()->url()]
                );
                
                $rankingChanges->withQueryString();
            } else {
                // 使用Eloquent查询时的分页，包含关联加载
                $rankingChanges = $query->with(['websiteIntroduction:domain,registered_at,title,description'])
                    ->paginate($perPage)
                    ->withQueryString();
            }

            // 缓存今日记录总数
            $cacheKey = "ranking_count_{$today}";
            $todayCount = Cache::remember($cacheKey, 300, function() use ($today) {
                return RankingChange::whereDate('record_date', $today)->count();
            });

            // 计算过滤后的记录数
            $filteredCount = $todayCount;
            if ($filterField && $filterValue !== null && $filterValue !== '') {
                // 只在有筛选条件时重新计算
                if ($needsJoin) {
                    // 克隆查询以避免影响分页查询
                    $countQuery = clone $query;
                    // 移除排序和分页相关的部分
                    $countQuery->orders = null;
                    $countQuery->limit = null;
                    $countQuery->offset = null;
                    $filteredCount = $countQuery->count();
                } else {
                    $filteredCount = $query->count();
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
            
            // 变化字段列表
            $changeFields = [
                'daily_change' => 'max_daily_change',
                'week_change' => 'max_week_change',
                'biweek_change' => 'max_biweek_change',
                'triweek_change' => 'max_triweek_change',
                'month_change' => 'max_month_change',
                'quarter_change' => 'max_quarter_change'
            ];
            
            foreach ($changeFields as $field => $resultKey) {
                // 分别获取最大正值和最小负值，然后比较绝对值
                $maxPositive = DB::table('ranking_changes')
                    ->where('record_date', $date)
                    ->where($field, '>', 0)
                    ->max($field);
                    
                $minNegative = DB::table('ranking_changes')
                    ->where('record_date', $date)
                    ->where($field, '<', 0)
                    ->min($field);
                
                // 比较绝对值，返回绝对值较大的
                $maxPositiveAbs = $maxPositive ? abs($maxPositive) : 0;
                $minNegativeAbs = $minNegative ? abs($minNegative) : 0;
                
                $result[$resultKey] = max($maxPositiveAbs, $minNegativeAbs);
            }
            
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
                        'biweek_change',
                        'biweek_trend',
                        'triweek_change',
                        'triweek_trend',
                        'month_change',
                        'month_trend',
                        'quarter_change',
                        'quarter_trend'
                    ]);
            });

            // 获取网站介绍信息
            $introduction = \App\Models\WebsiteIntroduction::where('domain', $domain)
                ->select('domain', 'registered_at', 'title', 'description')
                ->first();

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
     * 获取统计信息
     */
    public function getStats(Request $request)
    {
        try {
            $today = now()->format('Y-m-d');
            
            // 基础统计查询
            $baseStats = DB::table('ranking_changes')
                ->where('record_date', $today)
                ->select([
                    DB::raw('COUNT(*) as total_domains'),
                    DB::raw('AVG(current_ranking) as avg_ranking'),
                    DB::raw('MIN(current_ranking) as best_ranking'),
                    DB::raw('MAX(current_ranking) as worst_ranking'),
                    
                    // 趋势统计
                    DB::raw('COUNT(CASE WHEN daily_trend = "up" THEN 1 END) as daily_up'),
                    DB::raw('COUNT(CASE WHEN daily_trend = "down" THEN 1 END) as daily_down'),
                    DB::raw('COUNT(CASE WHEN week_trend = "up" THEN 1 END) as week_up'),
                    DB::raw('COUNT(CASE WHEN week_trend = "down" THEN 1 END) as week_down'),
                    DB::raw('COUNT(CASE WHEN month_trend = "up" THEN 1 END) as month_up'),
                    DB::raw('COUNT(CASE WHEN month_trend = "down" THEN 1 END) as month_down'),
                    
                    // 大幅变化统计（变化超过100名）
                    DB::raw('COUNT(CASE WHEN ABS(daily_change) > 100 THEN 1 END) as daily_big_change'),
                    DB::raw('COUNT(CASE WHEN ABS(week_change) > 100 THEN 1 END) as week_big_change'),
                    DB::raw('COUNT(CASE WHEN ABS(month_change) > 100 THEN 1 END) as month_big_change')
                ])
                ->first();

            // 获取最大变化值
            $maxChanges = $this->getMaxChanges($today);
            
            // 合并结果
            $stats = (object) array_merge(
                (array) $baseStats,
                $maxChanges
            );

            return response()->json($stats);

        } catch (\Exception $e) {
            \Log::error('Get stats error: ' . $e->getMessage());
            return response()->json(['error' => '统计信息获取失败'], 500);
        }
    }

    /**
     * 导出筛选后的数据
     */
    public function export(Request $request)
    {
        try {
            $today = now()->format('Y-m-d');
            
            // 获取查询参数
            $filterField = $request->get('filter_field');
            $filterValue = $request->get('filter_value');
            $sortBy = $request->get('sort', 'current_ranking');
            $sortOrder = $request->get('order', 'asc');
            
            // 构建查询（复用 index 方法的逻辑）
            $needsJoin = in_array($filterField, ['registered_after', 'registered_before']) || 
                        $sortBy === 'registered_at';
            
            if ($needsJoin) {
                $query = DB::table('ranking_changes')
                    ->leftJoin('website_introductions', 'ranking_changes.domain', '=', 'website_introductions.domain')
                    ->whereDate('ranking_changes.record_date', $today)
                    ->select('ranking_changes.*', 'website_introductions.registered_at');
            } else {
                $query = RankingChange::whereDate('record_date', $today);
            }
            
            // 应用筛选条件（复用 index 方法的筛选逻辑）
            // ... [筛选逻辑同 index 方法]
            
            // 应用排序
            // ... [排序逻辑同 index 方法]
            
            // 生成CSV响应
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="ranking_changes_' . $today . '.csv"',
            ];
            
            return response()->stream(function() use ($query, $needsJoin) {
                $handle = fopen('php://output', 'w');
                
                // 写入表头
                fputcsv($handle, [
                    'Domain',
                    'Current Ranking',
                    'Daily Change',
                    'Daily Trend',
                    'Week Change',
                    'Week Trend',
                    'Biweek Change',
                    'Biweek Trend',
                    'Triweek Change',
                    'Triweek Trend',
                    'Month Change',
                    'Month Trend',
                    'Quarter Change',
                    'Quarter Trend',
                    'Registered At'
                ]);
                
                // 分块处理数据，避免内存溢出
                if ($needsJoin) {
                    // DB查询的分块处理
                    $query->orderBy('ranking_changes.current_ranking', 'asc')
                          ->chunk(1000, function($records) use ($handle) {
                        foreach ($records as $record) {
                            fputcsv($handle, [
                                $record->domain,
                                $record->current_ranking,
                                $record->daily_change,
                                $record->daily_trend,
                                $record->week_change,
                                $record->week_trend,
                                $record->biweek_change,
                                $record->biweek_trend,
                                $record->triweek_change,
                                $record->triweek_trend,
                                $record->month_change,
                                $record->month_trend,
                                $record->quarter_change,
                                $record->quarter_trend,
                                $record->registered_at ?? ''
                            ]);
                        }
                    });
                } else {
                    // Eloquent查询的分块处理
                    $query->with('websiteIntroduction:domain,registered_at')
                          ->chunk(1000, function($records) use ($handle) {
                        foreach ($records as $record) {
                            fputcsv($handle, [
                                $record->domain,
                                $record->current_ranking,
                                $record->daily_change,
                                $record->daily_trend,
                                $record->week_change,
                                $record->week_trend,
                                $record->biweek_change,
                                $record->biweek_trend,
                                $record->triweek_change,
                                $record->triweek_trend,
                                $record->month_change,
                                $record->month_trend,
                                $record->quarter_change,
                                $record->quarter_trend,
                                $record->websiteIntroduction->registered_at ?? ''
                            ]);
                        }
                    });
                }
                
                fclose($handle);
            }, 200, $headers);
            
        } catch (\Exception $e) {
            \Log::error('Export error: ' . $e->getMessage());
            return redirect()->back()->with('error', '导出失败：' . $e->getMessage());
        }
    }
}