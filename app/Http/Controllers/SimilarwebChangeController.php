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

            $currentMonth = SimilarwebChange::find(1)->record_month;
            // $recordMonth = $request->get('month', Carbon::now()->subMonth()->format('Y-m'));
            $recordMonth = $request->get('month', $currentMonth);
            
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

            // 应用数值筛选 - 优化版本，避免使用ABS()函数
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
                        // 变化值筛选：优化版本
                        // 将 ABS(field) >= value 改为 (field >= value OR field <= -value)
                        // 这样可以利用索引
                        $query->where(function($q) use ($filterField, $filterValue) {
                            $q->where($filterField, '>=', $filterValue)
                              ->orWhere($filterField, '<=', -$filterValue);
                        });
                    }
                }
            }

            // 应用排序 - 优化版本
            if ($sortBy === 'domain' || $sortBy === 'current_emv') {
                // 直接字段排序，可以利用索引
                $query->orderBy($sortBy, $sortOrder);
            } else {
                // 对于变化字段的排序优化
                if ($sortOrder === 'desc') {
                    // 降序：增长最多的优先
                    // 使用索引友好的排序方式
                    $query->orderByRaw("
                        CASE 
                            WHEN $sortBy IS NULL THEN 2
                            WHEN $sortBy >= 0 THEN 0
                            ELSE 1
                        END,
                        $sortBy DESC
                    ");
                } else {
                    // 升序：下降最多的优先
                    // 使用索引友好的排序方式
                    $query->orderByRaw("
                        CASE 
                            WHEN $sortBy IS NULL THEN 2
                            WHEN $sortBy <= 0 THEN 0
                            ELSE 1
                        END,
                        $sortBy ASC
                    ");
                }
            }

            // 分页查询
            $similarwebChanges = $query->paginate(100)->withQueryString();
            
            // 获取统计信息 - 优化版本
            $statsQuery = SimilarwebChange::where('record_month', $recordMonth);
            
            // 基础统计
            $monthCount = $statsQuery->count();
            
            // 计算过滤后的记录数
            $filteredCount = $monthCount;
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
                    
                    $countQuery = SimilarwebChange::where('record_month', $recordMonth);
                    if ($filterField === 'current_emv') {
                        $countQuery->where($filterField, '>=', $filterValue);
                    } else {
                        // 使用索引友好的计数查询
                        $countQuery->where(function($q) use ($filterField, $filterValue) {
                            $q->where($filterField, '>=', $filterValue)
                              ->orWhere($filterField, '<=', -$filterValue);
                        });
                    }
                    $filteredCount = $countQuery->count();
                }
            }
            
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
     * 获取EMV变化统计信息 - 优化版本
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStats(Request $request)
    {
        try {

            $currentMonth = SimilarwebChange::find(1)->record_month;
            // $month = $request->get('month', Carbon::now()->subMonth()->format('Y-m'));
            $month = $request->get('month', $currentMonth);
            
            // 基础统计查询
            $baseStats = DB::table('similarweb_changes')
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
                    DB::raw('COUNT(CASE WHEN year_emv_trend = "down" THEN 1 END) as year_down')
                ])
                ->first();

            // 获取最大变化值 - 优化版本，分别查询正负最大值
            $maxChanges = $this->getMaxChanges($month);
            
            // 合并结果
            $stats = (object) array_merge(
                (array) $baseStats,
                $maxChanges
            );

            return response()->json($stats);

        } catch (\Exception $e) {
            return response()->json(['error' => '统计信息获取失败'], 500);
        }
    }

    /**
     * 获取最大变化值 - 索引友好版本
     *
     * @param string $month
     * @return array
     */
    private function getMaxChanges(string $month): array
    {
        $result = [];
        
        // 变化字段列表
        $changeFields = [
            'month_emv_change' => 'max_month_change',
            'quarter_emv_change' => 'max_quarter_change', 
            'halfyear_emv_change' => 'max_halfyear_change',
            'year_emv_change' => 'max_year_change'
        ];
        
        foreach ($changeFields as $field => $resultKey) {
            // 分别获取最大正值和最小负值，然后比较绝对值
            $maxPositive = DB::table('similarweb_changes')
                ->where('record_month', $month)
                ->where($field, '>', 0)
                ->max($field);
                
            $minNegative = DB::table('similarweb_changes')
                ->where('record_month', $month)
                ->where($field, '<', 0)
                ->min($field);
            
            // 比较绝对值，返回绝对值较大的
            $maxPositiveAbs = $maxPositive ? abs($maxPositive) : 0;
            $minNegativeAbs = $minNegative ? abs($minNegative) : 0;
            
            $result[$resultKey] = max($maxPositiveAbs, $minNegativeAbs);
        }
        
        return $result;
    }

    /**
     * 获取域名的历史变化趋势
     *
     * @param Request $request
     * @param string $domain
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDomainHistory(Request $request, string $domain)
    {
        try {
            // 使用索引友好的查询
            $history = SimilarwebChange::where('domain', $domain)
                ->orderBy('record_month', 'desc')
                ->limit(12) // 最近12个月
                ->get([
                    'record_month',
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

            return response()->json([
                'domain' => $domain,
                'history' => $history
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => '获取历史数据失败'], 500);
        }
    }

    /**
     * 导出筛选后的数据
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function export(Request $request)
    {
        try {
            // 获取查询参数
            $filterField = $request->get('filter_field');
            $filterValue = $request->get('filter_value');
            $currentMonth = SimilarwebChange::find(1)->record_month;
            // $recordMonth = $request->get('month', Carbon::now()->subMonth()->format('Y-m'));
            $recordMonth = $request->get('month', $currentMonth);
            $sortBy = $request->get('sort', 'current_emv');
            $sortOrder = $request->get('order', 'desc');
            
            // 构建查询
            $query = SimilarwebChange::where('record_month', $recordMonth);
            
            // 应用筛选条件（使用索引友好的方式）
            if ($filterField && $filterValue !== null && $filterValue !== '') {
                $filterValue = (int)$filterValue;
                
                if ($filterField === 'current_emv') {
                    $query->where($filterField, '>=', $filterValue);
                } elseif (in_array($filterField, ['month_emv_change', 'quarter_emv_change', 'halfyear_emv_change', 'year_emv_change'])) {
                    $query->where(function($q) use ($filterField, $filterValue) {
                        $q->where($filterField, '>=', $filterValue)
                          ->orWhere($filterField, '<=', -$filterValue);
                    });
                }
            }
            
            // 应用排序
            if (in_array($sortBy, ['domain', 'current_emv'])) {
                $query->orderBy($sortBy, $sortOrder);
            } else {
                // 使用简化的排序逻辑
                $query->orderBy($sortBy, $sortOrder);
            }
            
            // 生成CSV响应
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="emv_changes_' . $recordMonth . '.csv"',
            ];
            
            return response()->stream(function() use ($query) {
                $handle = fopen('php://output', 'w');
                
                // 写入表头
                fputcsv($handle, [
                    'Domain',
                    'Current EMV',
                    'Month Change',
                    'Month Trend',
                    'Quarter Change',
                    'Quarter Trend',
                    'Half Year Change',
                    'Half Year Trend',
                    'Year Change',
                    'Year Trend'
                ]);
                
                // 分块处理数据，避免内存溢出
                $query->chunk(1000, function($records) use ($handle) {
                    foreach ($records as $record) {
                        fputcsv($handle, [
                            $record->domain,
                            $record->current_emv,
                            $record->month_emv_change,
                            $record->month_emv_trend,
                            $record->quarter_emv_change,
                            $record->quarter_emv_trend,
                            $record->halfyear_emv_change,
                            $record->halfyear_emv_trend,
                            $record->year_emv_change,
                            $record->year_emv_trend
                        ]);
                    }
                });
                
                fclose($handle);
            }, 200, $headers);
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', '导出失败：' . $e->getMessage());
        }
    }
}