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
            $sortBy = $request->get('sort', 'record_date');
            $sortOrder = $request->get('order', 'desc');
            $filterField = $request->get('filter_field');
            $filterValue = $request->get('filter_value');
            $dateFilter = $request->get('date_filter');
            
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
                $sortBy = 'record_date';
            }
            
            // 验证排序顺序
            if (!in_array($sortOrder, ['asc', 'desc'])) {
                $sortOrder = 'desc';
            }
            
            // 验证筛选操作符 - 移除，简化为只使用 ">="
            // $allowedOperators = ['>', '<', '>=', '<=', '=', '!='];
            // if (!in_array($filterOperator, $allowedOperators)) {
            //     $filterOperator = '>=';
            // }

            // 构建查询
            $query = RankingChange::select([
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

            // 应用日期筛选
            if ($dateFilter) {
                switch ($dateFilter) {
                    case 'today':
                        $query->whereDate('record_date', today());
                        break;
                    case 'yesterday':
                        $query->whereDate('record_date', today()->subDay());
                        break;
                    case 'last_7_days':
                        $query->where('record_date', '>=', today()->subDays(7));
                        break;
                    case 'last_30_days':
                        $query->where('record_date', '>=', today()->subDays(30));
                        break;
                    case 'this_month':
                        $query->whereMonth('record_date', now()->month)
                              ->whereYear('record_date', now()->year);
                        break;
                    case 'last_month':
                        $lastMonth = now()->subMonth();
                        $query->whereMonth('record_date', $lastMonth->month)
                              ->whereYear('record_date', $lastMonth->year);
                        break;
                }
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
                'dateFilter',
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