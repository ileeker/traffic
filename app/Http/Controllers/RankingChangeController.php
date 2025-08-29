<?php

namespace App\Http\Controllers;

use App\Models\RankingChange;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        try {
            // 获取查询参数
            $sortBy = $request->get('sort', 'current_ranking');
            $sortOrder = $request->get('order', 'asc');
            $filterField = $request->get('filter_field');
            $filterValue = $request->get('filter_value');
            $trendFilter = $request->get('trend_filter', 'up'); // 默认只看上升的
            
            // 验证排序字段
            $allowedSorts = [
                'record_date',
                'domain',
                'current_ranking',
                'daily_change',
                'week_change',
                'biweek_change',
                'triweek_change',
                'month_change',
                'quarter_change',
                'year_change'
            ];
            
            if (!in_array($sortBy, $allowedSorts)) {
                $sortBy = 'current_ranking';
            }
            
            // 验证排序顺序
            if (!in_array($sortOrder, ['asc', 'desc'])) {
                $sortOrder = 'asc';
            }

            // 构建查询 - 只查询今天的数据
            $query = RankingChange::whereDate('record_date', today())
                ->select([
                    'id',
                    'domain',
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
                    'quarter_trend',
                    'year_change',
                    'year_trend'
                ]);

            // 应用趋势筛选 - 默认只看上升的排名
            if ($trendFilter && $trendFilter !== 'all') {
                $query->where(function($q) use ($trendFilter) {
                    if ($trendFilter === 'up') {
                        // 只看排名上升的（任意时间段有上升趋势）
                        $q->where('daily_trend', 'up')
                          ->orWhere('week_trend', 'up')
                          ->orWhere('biweek_trend', 'up')
                          ->orWhere('triweek_trend', 'up')
                          ->orWhere('month_trend', 'up')
                          ->orWhere('quarter_trend', 'up')
                          ->orWhere('year_trend', 'up');
                    } elseif ($trendFilter === 'down') {
                        // 只看排名下降的
                        $q->where('daily_trend', 'down')
                          ->orWhere('week_trend', 'down')
                          ->orWhere('biweek_trend', 'down')
                          ->orWhere('triweek_trend', 'down')
                          ->orWhere('month_trend', 'down')
                          ->orWhere('quarter_trend', 'down')
                          ->orWhere('year_trend', 'down');
                    }
                });
            }

            // 应用数值筛选
            if ($filterField && $filterValue !== null && $filterValue !== '') {
                $filterFields = [
                    'current_ranking',
                    'daily_change',
                    'week_change',
                    'biweek_change',
                    'triweek_change',
                    'month_change',
                    'quarter_change',
                    'year_change'
                ];
                
                if (in_array($filterField, $filterFields)) {
                    $filterValue = (int)$filterValue;
                    $query->where($filterField, '>=', $filterValue);
                }
            }

            // 应用排序
            if ($sortBy === 'domain') {
                $query->orderBy($sortBy, $sortOrder);
            } else {
                // 对于数值字段，NULL值排在最后
                if (in_array($sortBy, ['daily_change', 'week_change', 'biweek_change', 'triweek_change', 'month_change', 'quarter_change', 'year_change'])) {
                    $query->orderByRaw("$sortBy IS NULL")
                          ->orderBy($sortBy, $sortOrder);
                } else {
                    $query->orderBy($sortBy, $sortOrder);
                }
            }

            // 分页查询
            $rankingChanges = $query->paginate(100)->withQueryString();
            
            // 获取统计信息
            $totalCount = RankingChange::count();
            $todayCount = RankingChange::whereDate('record_date', today())->count();
            
            // 计算过滤后的记录数
            $filteredQuery = clone $query;
            $filteredCount = $filteredQuery->count();

            // 获取最新记录日期
            $latestDate = RankingChange::max('record_date');

            return view('ranking-changes.index', compact(
                'rankingChanges',
                'sortBy',
                'sortOrder',
                'filterField',
                'filterValue',
                'trendFilter',
                'totalCount',
                'todayCount',
                'filteredCount',
                'latestDate'
            ));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', '数据加载失败：' . $e->getMessage());
        }
    }

    /**
     * 获取排名变化统计信息
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStats(Request $request)
    {
        try {
            $date = $request->get('date', today());
            
            $stats = DB::table('ranking_changes')
                ->where('record_date', $date)
                ->select([
                    DB::raw('COUNT(*) as total_domains'),
                    DB::raw('COUNT(CASE WHEN daily_trend = "up" THEN 1 END) as daily_up'),
                    DB::raw('COUNT(CASE WHEN daily_trend = "down" THEN 1 END) as daily_down'),
                    DB::raw('COUNT(CASE WHEN daily_trend = "stable" THEN 1 END) as daily_stable'),
                    DB::raw('COUNT(CASE WHEN week_trend = "up" THEN 1 END) as week_up'),
                    DB::raw('COUNT(CASE WHEN week_trend = "down" THEN 1 END) as week_down'),
                    DB::raw('COUNT(CASE WHEN week_trend = "stable" THEN 1 END) as week_stable'),
                    DB::raw('AVG(current_ranking) as avg_ranking'),
                    DB::raw('MAX(ABS(daily_change)) as max_daily_change'),
                    DB::raw('MAX(ABS(week_change)) as max_week_change')
                ])
                ->first();

            return response()->json($stats);

        } catch (\Exception $e) {
            return response()->json(['error' => '统计信息获取失败'], 500);
        }
    }
}