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
                    
                    // 对于排名变化字段，使用绝对值过滤
                    if ($filterField !== 'current_ranking') {
                        $query->whereRaw("ABS($filterField) >= ?", [$filterValue]);
                    } else {
                        $query->where($filterField, '<=', $filterValue);
                    }
                }
            }

            // 应用排序 - 上升优先（正数优先）
            if ($sortBy === 'domain' || $sortBy === 'current_ranking') {
                $query->orderBy($sortBy, $sortOrder);
            } else {
                // 对于变化字段，先按上升（正数）排序，再按下降（负数）排序
                if ($sortOrder === 'desc') {
                    // 降序：大的正数优先，然后是小的正数，然后是小的负数，最后是大的负数
                    $query->orderByRaw("
                        CASE 
                            WHEN $sortBy IS NULL THEN 3
                            WHEN $sortBy > 0 THEN 1
                            WHEN $sortBy = 0 THEN 2
                            ELSE 2
                        END,
                        CASE 
                            WHEN $sortBy > 0 THEN -$sortBy
                            ELSE $sortBy
                        END ASC
                    ");
                } else {
                    // 升序：小的正数优先，然后是大的正数，然后是大的负数，最后是小的负数
                    $query->orderByRaw("
                        CASE 
                            WHEN $sortBy IS NULL THEN 3
                            WHEN $sortBy > 0 THEN 1
                            WHEN $sortBy = 0 THEN 2
                            ELSE 2
                        END,
                        CASE 
                            WHEN $sortBy > 0 THEN $sortBy
                            ELSE -$sortBy
                        END DESC
                    ");
                }
            }

            // 分页查询
            $rankingChanges = $query->paginate(100)->withQueryString();
            
            // 获取统计信息
            $todayCount = RankingChange::whereDate('record_date', today())->count();
            
            // 计算过滤后的记录数
            $filteredQuery = clone $query;
            $filteredCount = $filteredQuery->count();

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