<?php

namespace App\Http\Controllers;

use App\Models\SimilarwebChange;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SimilarwebChangeController extends Controller
{
    /**
     * 展示EMV变化数据列表
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        try {
            // 获取查询参数
            $sortBy = $request->get('sort', 'current_emv');
            $sortOrder = $request->get('order', 'desc');
            $filterField = $request->get('filter_field');
            $filterValue = $request->get('filter_value');
            $trendFilter = $request->get('trend_filter');
            $recordMonth = $request->get('month', Carbon::now()->subMonth()->format('Y-m'));
            
            // 验证排序字段
            $allowedSorts = [
                'domain',
                'current_emv',
                'month_emv_change',
                'quarter_emv_change',
                'halfyear_emv_change',
                'year_emv_change'
            ];
            
            if (!in_array($sortBy, $allowedSorts)) {
                $sortBy = 'current_emv';
            }
            
            // 验证排序顺序
            if (!in_array($sortOrder, ['asc', 'desc'])) {
                $sortOrder = 'desc';
            }

            // 构建查询 - 查询指定月份的数据
            $query = SimilarwebChange::where('record_month', $recordMonth)
                ->select([
                    'id',
                    'domain',
                    'current_emv',
                    'month_emv_change',
                    'month_emv_trend',
                    'quarter_emv_change',
                    'quarter_emv_trend',
                    'halfyear_emv_change',
                    'halfyear_emv_trend',
                    'year_emv_change',
                    'year_emv_trend'
                ]);

            // 应用趋势过滤
            if ($trendFilter && $trendFilter !== 'all') {
                $trendFields = [
                    'month' => 'month_emv_trend',
                    'quarter' => 'quarter_emv_trend',
                    'halfyear' => 'halfyear_emv_trend',
                    'year' => 'year_emv_trend'
                ];
                
                // 解析过滤器格式
                $parts = explode('_', $trendFilter);
                if (count($parts) == 2) {
                    $period = $parts[0];
                    $trend = $parts[1];
                    
                    if (isset($trendFields[$period]) && in_array($trend, ['up', 'down', 'stable'])) {
                        $query->where($trendFields[$period], $trend);
                    }
                } elseif ($trendFilter === 'any_up') {
                    // 任意时间段上升
                    $query->where(function($q) use ($trendFields) {
                        foreach ($trendFields as $field) {
                            $q->orWhere($field, 'up');
                        }
                    });
                } elseif ($trendFilter === 'all_up') {
                    // 所有时间段都上升
                    $query->where(function($q) use ($trendFields) {
                        foreach ($trendFields as $field) {
                            $q->where($field, 'up');
                        }
                    });
                }
            }

            // 应用数值筛选
            if ($filterField && $filterValue !== null && $filterValue !== '') {
                $filterFields = [
                    'current_emv',
                    'month_emv_change',
                    'quarter_emv_change',
                    'halfyear_emv_change',
                    'year_emv_change'
                ];
                
                if (in_array($filterField, $filterFields)) {
                    $filterValue = (int)$filterValue;
                    
                    if ($filterField === 'current_emv') {
                        // EMV值筛选（大于等于）
                        $query->where($filterField, '>=', $filterValue);
                    } else {
                        // 变化值筛选（绝对值大于等于）
                        $query->whereRaw("ABS($filterField) >= ?", [$filterValue]);
                    }
                }
            }

            // 应用排序
            if ($sortBy === 'domain') {
                $query->orderBy($sortBy, $sortOrder);
            } elseif ($sortBy === 'current_emv') {
                $query->orderBy($sortBy, $sortOrder);
            } else {
                // 对于变化字段：正数=增长，负数=下降（与排名相反）
                if ($sortOrder === 'desc') {
                    // 降序：增长最多的优先（最大的正数优先）
                    $query->orderByRaw("
                        CASE 
                            WHEN $sortBy IS NULL THEN 3
                            WHEN $sortBy > 0 THEN 1  -- 正数（增长）优先
                            WHEN $sortBy = 0 THEN 2
                            ELSE 2  -- 负数（下降）最后
                        END,
                        CASE 
                            WHEN $sortBy > 0 THEN -$sortBy  -- 正数按负值排序
                            ELSE $sortBy  -- 负数按原值排序
                        END ASC
                    ");
                } else {
                    // 升序：下降最多的优先（最大的负数优先）
                    $query->orderByRaw("
                        CASE 
                            WHEN $sortBy IS NULL THEN 3
                            WHEN $sortBy < 0 THEN 1  -- 负数（下降）优先
                            WHEN $sortBy = 0 THEN 2
                            ELSE 2  -- 正数（增长）最后
                        END,
                        CASE 
                            WHEN $sortBy < 0 THEN $sortBy  -- 负数按原值排序
                            ELSE -$sortBy  -- 正数按负值排序
                        END ASC
                    ");
                }
            }

            // 分页查询
            $similarwebChanges = $query->paginate(100)->withQueryString();
            
            // 获取统计信息
            $monthCount = SimilarwebChange::where('record_month', $recordMonth)->count();
            
            // 计算过滤后的记录数
            $filteredQuery = clone $query;
            $filteredCount = $filteredQuery->count();
            
            // 获取可用的月份列表
            $availableMonths = SimilarwebChange::select('record_month')
                ->distinct()
                ->orderBy('record_month', 'desc')
                ->pluck('record_month');

            return view('similarweb-changes.index', compact(
                'similarwebChanges',
                'sortBy',
                'sortOrder',
                'filterField',
                'filterValue',
                'trendFilter',
                'recordMonth',
                'monthCount',
                'filteredCount',
                'availableMonths'
            ));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', '数据加载失败：' . $e->getMessage());
        }
    }

    /**
     * 获取EMV变化统计信息
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStats(Request $request)
    {
        try {
            $month = $request->get('month', Carbon::now()->subMonth()->format('Y-m'));
            
            $stats = DB::table('similarweb_changes')
                ->where('record_month', $month)
                ->select([
                    DB::raw('COUNT(*) as total_domains'),
                    DB::raw('AVG(current_emv) as avg_emv'),
                    DB::raw('MAX(current_emv) as max_emv'),
                    DB::raw('MIN(current_emv) as min_emv'),
                    DB::raw('COUNT(CASE WHEN month_emv_trend = "up" THEN 1 END) as month_up'),
                    DB::raw('COUNT(CASE WHEN month_emv_trend = "down" THEN 1 END) as month_down'),
                    DB::raw('COUNT(CASE WHEN quarter_emv_trend = "up" THEN 1 END) as quarter_up'),
                    DB::raw('COUNT(CASE WHEN quarter_emv_trend = "down" THEN 1 END) as quarter_down'),
                    DB::raw('COUNT(CASE WHEN year_emv_trend = "up" THEN 1 END) as year_up'),
                    DB::raw('COUNT(CASE WHEN year_emv_trend = "down" THEN 1 END) as year_down'),
                    DB::raw('MAX(ABS(month_emv_change)) as max_month_change'),
                    DB::raw('MAX(ABS(quarter_emv_change)) as max_quarter_change'),
                    DB::raw('MAX(ABS(year_emv_change)) as max_year_change')
                ])
                ->first();

            return response()->json($stats);

        } catch (\Exception $e) {
            return response()->json(['error' => '统计信息获取失败'], 500);
        }
    }
}