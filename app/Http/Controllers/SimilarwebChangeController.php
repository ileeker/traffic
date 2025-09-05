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
            $recordMonth = $request->get('month', $currentMonth);
            
            // 验证排序字段
            $allowedSorts = [
                'domain', 'current_emv', 'month_emv_change', 'month_emv_growth_rate',
                'quarter_emv_change', 'quarter_emv_growth_rate', 'halfyear_emv_change',
                'halfyear_emv_growth_rate', 'year_emv_change', 'year_emv_growth_rate',
                'registered_at'
            ];
            
            if (!in_array($sortBy, $allowedSorts)) {
                $sortBy = 'current_emv';
            }
            
            // 验证排序顺序
            if (!in_array($sortOrder, ['asc', 'desc'])) {
                $sortOrder = 'desc';
            }

            // [修复] 决定是否需要JOIN查询: 当按注册时间排序或过滤时都需要
            $needsJoin = ($sortBy === 'registered_at') || in_array($filterField, ['registered_after', 'registered_before']);

            // 构建查询
            if ($needsJoin) {
                // 需要注册时间排序或过滤时，使用JOIN查询
                $query = DB::table('similarweb_changes')
                    ->leftJoin('website_introductions', 'similarweb_changes.domain', '=', 'website_introductions.domain')
                    ->where('similarweb_changes.record_month', $recordMonth)
                    ->select(
                        'similarweb_changes.*', // 选择所有字段
                        'website_introductions.registered_at'
                    );
            } else {
                // 不需要时，使用Eloquent查询以获得更好的性能和便利性
                $query = SimilarwebChange::where('record_month', $recordMonth);
            }

            // 应用筛选
            if ($filterField && $filterValue !== null && $filterValue !== '') {
                $numericFilterFields = [
                    'current_emv', 'month_emv_change', 'quarter_emv_change', 'halfyear_emv_change', 'year_emv_change',
                    'month_emv_growth_rate', 'quarter_emv_growth_rate', 'halfyear_emv_growth_rate', 'year_emv_growth_rate'
                ];
                
                if (in_array($filterField, $numericFilterFields)) {
                    $fieldName = $needsJoin ? "similarweb_changes.$filterField" : $filterField;
                    if (strpos($filterField, 'growth_rate') !== false) {
                        $query->where($fieldName, '>=', (float)$filterValue);
                    } else {
                        $query->where($fieldName, '>=', (int)$filterValue);
                    }
                } 
                // [新增] 处理注册日期过滤
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
                    }
                }
            }

            // 应用排序
            if ($sortBy === 'registered_at') {
                $query->orderBy('website_introductions.registered_at', $sortOrder);
            } else {
                $fieldName = $needsJoin ? "similarweb_changes.$sortBy" : $sortBy;
                $query->orderBy($fieldName, $sortOrder);
            }
            
            // 分页查询
            if ($needsJoin) {
                $similarwebChanges = $query->paginate(100);
                // 手动加载关联关系 (因为DB查询不会自动创建Eloquent模型)
                $domains = collect($similarwebChanges->items())->pluck('domain')->toArray();
                $websiteIntroductions = \App\Models\WebsiteIntroduction::whereIn('domain', $domains)
                    ->select('domain', 'registered_at')
                    ->get()
                    ->keyBy('domain');
                
                // 将关联数据附加到结果中
                foreach ($similarwebChanges->items() as $item) {
                    $item->websiteIntroduction = $websiteIntroductions->get($item->domain);
                }
                $similarwebChanges->withQueryString();
            } else {
                // 使用Eloquent查询时的分页，包含关联加载
                $similarwebChanges = $query->with(['websiteIntroduction:domain,registered_at'])
                    ->paginate(100)
                    ->withQueryString();
            }
            
            // 获取统计信息
            $monthCount = SimilarwebChange::where('record_month', $recordMonth)->count();
            
            // [修复] 计算过滤后的记录数
            $filteredCount = $monthCount;
            if ($filterField && $filterValue !== null && $filterValue !== '') {
                // 使用主查询的克隆来获取过滤后的总数，避免重复逻辑
                $countQuery = clone $query;
                $filteredCount = $countQuery->count();
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

    // ... 控制器中的其他方法保持不变 ...

    /**
     * 获取EMV变化统计信息 - 增强版本
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStats(Request $request)
    {
        try {
            $currentMonth = SimilarwebChange::find(1)->record_month;
            $month = $request->get('month', $currentMonth);
            
            // 基础统计查询 - 包含增长率统计
            $baseStats = DB::table('similarweb_changes')
                ->where('record_month', $month)
                ->select([
                    DB::raw('COUNT(*) as total_domains'),
                    DB::raw('AVG(current_emv) as avg_emv'),
                    DB::raw('MAX(current_emv) as max_emv'),
                    DB::raw('MIN(current_emv) as min_emv'),
                    
                    // 趋势统计
                    DB::raw('COUNT(CASE WHEN month_emv_trend = "up" THEN 1 END) as month_up'),
                    DB::raw('COUNT(CASE WHEN month_emv_trend = "down" THEN 1 END) as month_down'),
                    DB::raw('COUNT(CASE WHEN quarter_emv_trend = "up" THEN 1 END) as quarter_up'),
                    DB::raw('COUNT(CASE WHEN quarter_emv_trend = "down" THEN 1 END) as quarter_down'),
                    DB::raw('COUNT(CASE WHEN year_emv_trend = "up" THEN 1 END) as year_up'),
                    DB::raw('COUNT(CASE WHEN year_emv_trend = "down" THEN 1 END) as year_down'),
                    
                    // 增长率统计
                    DB::raw('AVG(month_emv_growth_rate) as avg_month_growth'),
                    DB::raw('AVG(quarter_emv_growth_rate) as avg_quarter_growth'),
                    DB::raw('AVG(year_emv_growth_rate) as avg_year_growth'),
                    DB::raw('MAX(month_emv_growth_rate) as max_month_growth'),
                    DB::raw('MAX(quarter_emv_growth_rate) as max_quarter_growth'),
                    DB::raw('MAX(year_emv_growth_rate) as max_year_growth'),
                    DB::raw('MIN(month_emv_growth_rate) as min_month_growth'),
                    DB::raw('MIN(quarter_emv_growth_rate) as min_quarter_growth'),
                    DB::raw('MIN(year_emv_growth_rate) as min_year_growth'),
                    
                    // 高增长统计（>20%）
                    DB::raw('COUNT(CASE WHEN month_emv_growth_rate > 20 THEN 1 END) as high_growth_month'),
                    DB::raw('COUNT(CASE WHEN quarter_emv_growth_rate > 20 THEN 1 END) as high_growth_quarter'),
                    DB::raw('COUNT(CASE WHEN year_emv_growth_rate > 20 THEN 1 END) as high_growth_year')
                ])
                ->first();

            // 获取最大变化值
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
                    'month_emv_growth_rate',
                    'quarter_emv_change',
                    'quarter_emv_trend',
                    'quarter_emv_growth_rate',
                    'halfyear_emv_change',
                    'halfyear_emv_trend',
                    'halfyear_emv_growth_rate',
                    'year_emv_change',
                    'year_emv_trend',
                    'year_emv_growth_rate'
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
            $recordMonth = $request->get('month', $currentMonth);
            $sortBy = $request->get('sort', 'current_emv');
            $sortOrder = $request->get('order', 'desc');
            
            // 构建查询
            $query = SimilarwebChange::where('record_month', $recordMonth);
            
            // 应用筛选条件
            if ($filterField && $filterValue !== null && $filterValue !== '') {
                if (strpos($filterField, 'growth_rate') !== false) {
                    $filterValue = (float)$filterValue;
                    if ($request->get('filter_type') === 'abs') {
                        $query->where(function($q) use ($filterField, $filterValue) {
                            $q->where($filterField, '>=', $filterValue)
                              ->orWhere($filterField, '<=', -$filterValue);
                        });
                    } else {
                        $query->where($filterField, '>=', $filterValue);
                    }
                } elseif ($filterField === 'current_emv') {
                    $filterValue = (int)$filterValue;
                    $query->where($filterField, '>=', $filterValue);
                } elseif (in_array($filterField, ['month_emv_change', 'quarter_emv_change', 'halfyear_emv_change', 'year_emv_change'])) {
                    $filterValue = (int)$filterValue;
                    $query->where(function($q) use ($filterField, $filterValue) {
                        $q->where($filterField, '>=', $filterValue)
                          ->orWhere($filterField, '<=', -$filterValue);
                    });
                }
            }
            
            // 应用排序
            $query->orderBy($sortBy, $sortOrder);
            
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
                    'Month Growth Rate (%)',
                    'Quarter Change',
                    'Quarter Trend',
                    'Quarter Growth Rate (%)',
                    'Half Year Change',
                    'Half Year Trend',
                    'Half Year Growth Rate (%)',
                    'Year Change',
                    'Year Trend',
                    'Year Growth Rate (%)'
                ]);
                
                // 分块处理数据，避免内存溢出
                $query->chunk(1000, function($records) use ($handle) {
                    foreach ($records as $record) {
                        fputcsv($handle, [
                            $record->domain,
                            $record->current_emv,
                            $record->month_emv_change,
                            $record->month_emv_trend,
                            $record->month_emv_growth_rate,
                            $record->quarter_emv_change,
                            $record->quarter_emv_trend,
                            $record->quarter_emv_growth_rate,
                            $record->halfyear_emv_change,
                            $record->halfyear_emv_trend,
                            $record->halfyear_emv_growth_rate,
                            $record->year_emv_change,
                            $record->year_emv_trend,
                            $record->year_emv_growth_rate
                        ]);
                    }
                });
                
                fclose($handle);
            }, 200, $headers);
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', '导出失败：' . $e->getMessage());
        }
    }
    
    /**
     * 获取增长率TOP排行榜
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getGrowthRankings(Request $request)
    {
        try {
            $currentMonth = SimilarwebChange::find(1)->record_month;
            $month = $request->get('month', $currentMonth);
            $period = $request->get('period', 'month'); // month, quarter, halfyear, year
            $limit = $request->get('limit', 10);
            
            $growthField = "{$period}_emv_growth_rate";
            
            // 获取增长最快的TOP N
            $topGrowth = SimilarwebChange::where('record_month', $month)
                ->whereNotNull($growthField)
                ->where($growthField, '>', 0)
                ->orderBy($growthField, 'desc')
                ->limit($limit)
                ->get(['domain', 'current_emv', $growthField]);
            
            // 获取下降最快的TOP N
            $topDecline = SimilarwebChange::where('record_month', $month)
                ->whereNotNull($growthField)
                ->where($growthField, '<', 0)
                ->orderBy($growthField, 'asc')
                ->limit($limit)
                ->get(['domain', 'current_emv', $growthField]);
            
            return response()->json([
                'period' => $period,
                'top_growth' => $topGrowth,
                'top_decline' => $topDecline
            ]);
            
        } catch (\Exception $e) {
            return response()->json(['error' => '获取排行榜失败'], 500);
        }
    }
}
