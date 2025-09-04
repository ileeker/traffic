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

            // 使用原生查询构建器以获得更好的性能
            $query = DB::table('ranking_changes')
                ->whereDate('record_date', $today)
                ->select([
                    'ranking_changes.id',
                    'ranking_changes.domain',
                    'ranking_changes.current_ranking',
                    'ranking_changes.daily_change',
                    'ranking_changes.daily_trend',
                    'ranking_changes.week_change',
                    'ranking_changes.week_trend',
                    'ranking_changes.biweek_change',
                    'ranking_changes.biweek_trend',
                    'ranking_changes.triweek_change',
                    'ranking_changes.triweek_trend',
                    'ranking_changes.month_change',
                    'ranking_changes.month_trend',
                    'ranking_changes.quarter_change',
                    'ranking_changes.quarter_trend',
                    'ranking_changes.year_change',
                    'ranking_changes.year_trend',
                    'ranking_changes.record_date' // 添加 record_date 以便使用索引
                ]);

            // 优化：如果需要注册时间，一次性 JOIN
            if ($sortBy === 'registered_at' || $request->has('show_registered_at')) {
                $query->leftJoin('website_introductions', 'ranking_changes.domain', '=', 'website_introductions.domain')
                      ->addSelect('website_introductions.registered_at', 
                                  'website_introductions.description as website_description');
            }

            // 优化后的筛选逻辑
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
                        // 使用索引 idx_date_ranking
                        $query->where('ranking_changes.current_ranking', '<=', $filterValue);
                    } else {
                        // 优化：使用 UNION 代替 OR 来提高性能
                        $positiveQuery = clone $query;
                        $negativeQuery = clone $query;
                        
                        $positiveQuery->where("ranking_changes.{$filterField}", '>=', $filterValue);
                        $negativeQuery->where("ranking_changes.{$filterField}", '<=', -$filterValue);
                        
                        $query = $positiveQuery->union($negativeQuery);
                    }
                }
            }

            // 优化后的排序逻辑
            if ($sortBy === 'domain') {
                $query->orderBy('ranking_changes.domain', $sortOrder);
            } elseif ($sortBy === 'current_ranking') {
                // 利用 idx_date_ranking 索引
                $query->orderBy('ranking_changes.current_ranking', $sortOrder);
            } elseif ($sortBy === 'registered_at') {
                // 优化：避免使用 ORDER BY RAW
                if ($sortOrder === 'asc') {
                    $query->orderBy('website_introductions.registered_at', 'asc');
                } else {
                    $query->orderBy('website_introductions.registered_at', 'desc');
                }
            } else {
                // 变化字段排序优化
                $query->orderBy("ranking_changes.{$sortBy}", 
                    $sortOrder === 'desc' ? 'asc' : 'desc');
            }

            // 添加二级排序以确保结果稳定
            if ($sortBy !== 'domain') {
                $query->orderBy('ranking_changes.domain', 'asc');
            }

            // 使用分页
            $perPage = $request->get('per_page', 100);
            $page = $request->get('page', 1);
            
            // 优化：使用缓存计算总数
            $cacheKey = "ranking_count_{$today}_{$filterField}_{$filterValue}";
            $total = Cache::remember($cacheKey, 300, function() use ($query) {
                return $query->count();
            });

            // 计算偏移量
            $offset = ($page - 1) * $perPage;
            
            // 获取数据
            $items = $query->offset($offset)
                          ->limit($perPage)
                          ->get();

            // 批量获取关联的 website_introductions（如果需要但没有 JOIN）
            if ($sortBy !== 'registered_at' && !$request->has('show_registered_at')) {
                $domains = $items->pluck('domain')->unique()->toArray();
                $websiteIntros = DB::table('website_introductions')
                    ->whereIn('domain', $domains)
                    ->select('domain', 'description', 'registered_at')
                    ->get()
                    ->keyBy('domain');
                
                // 将网站介绍信息附加到结果中
                $items = $items->map(function($item) use ($websiteIntros) {
                    $item->website_description = $websiteIntros->get($item->domain)->description ?? null;
                    $item->registered_at = $websiteIntros->get($item->domain)->registered_at ?? null;
                    return $item;
                });
            }

            // 创建分页器
            $rankingChanges = new \Illuminate\Pagination\LengthAwarePaginator(
                $items,
                $total,
                $perPage,
                $page,
                ['path' => request()->url()]
            );
            
            $rankingChanges->withQueryString();

            // 获取统计信息（使用缓存）
            $todayCount = Cache::remember("total_count_{$today}", 300, function() use ($today) {
                return DB::table('ranking_changes')
                    ->whereDate('record_date', $today)
                    ->count();
            });
            
            $filteredCount = $total;

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
            return redirect()->back()->with('error', '数据加载失败：' . $e->getMessage());
        }
    }
    
    /**
     * 获取域名的历史排名变化（优化版）
     */
    public function getDomainHistory(Request $request, string $domain)
    {
        try {
            // 使用缓存
            $cacheKey = "domain_history_{$domain}_" . now()->format('Y-m-d');
            
            $history = Cache::remember($cacheKey, 3600, function() use ($domain) {
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
            return response()->json(['error' => '获取历史数据失败'], 500);
        }
    }
}