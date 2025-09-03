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
                'registered_at'  // 添加注册时间排序
            ];
            
            if (!in_array($sortBy, $allowedSorts)) {
                $sortBy = 'current_ranking';
            }
            
            // 验证排序顺序
            if (!in_array($sortOrder, ['asc', 'desc'])) {
                $sortOrder = 'asc';
            }

            // 构建查询 - 只查询今天的数据，并预加载 websiteIntroduction 关联
            $query = RankingChange::whereDate('record_date', $today)
                ->with('websiteIntroduction')  // 添加预加载关联
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
                    'ranking_changes.year_trend'
                ]);

            // 如果需要按注册时间排序，添加 left join
            if ($sortBy === 'registered_at') {
                $query->leftJoin('website_introductions', 'ranking_changes.domain', '=', 'website_introductions.domain')
                      ->addSelect('website_introductions.registered_at');
            }

            // 应用数值筛选 - 优化版本，避免使用 ABS()
            if ($filterField && $filterValue !== null && $filterValue !== '') {
                $filterFields = [
                    'current_ranking',
                    'daily_change',
                    'week_change',
                    'biweek_change',
                    'triweek_change',
                    'month_change',
                    'quarter_change'
                    // 移除 year_change
                ];
                
                if (in_array($filterField, $filterFields)) {
                    $filterValue = (int)$filterValue;
                    
                    if ($filterField === 'current_ranking') {
                        // 排名筛选：小于等于
                        $query->where($filterField, '<=', $filterValue);
                    } else {
                        // 变化值筛选：优化版本
                        // 将 ABS(field) >= value 改为 (field >= value OR field <= -value)
                        $query->where(function($q) use ($filterField, $filterValue) {
                            $q->where($filterField, '>=', $filterValue)
                              ->orWhere($filterField, '<=', -$filterValue);
                        });
                    }
                }
            }

            // 应用排序 - 简化版本
            if ($sortBy === 'domain' || $sortBy === 'current_ranking') {
                // 直接字段排序
                $query->orderBy($sortBy, $sortOrder);
            } else if ($sortBy === 'registered_at') {
                // 注册时间排序
                // 需要先确保已经 join 了 website_introductions 表
                if (!$query->getQuery()->joins) {
                    $query->leftJoin('website_introductions', 'ranking_changes.domain', '=', 'website_introductions.domain');
                }
                // NULL 值放在最后
                $query->orderByRaw("website_introductions.registered_at IS NULL, website_introductions.registered_at $sortOrder");
            } else {
                // 变化字段排序：负数表示上升，正数表示下降
                if ($sortOrder === 'desc') {
                    // 降序：上升最多的优先（最大的负数）
                    $query->orderByRaw("$sortBy IS NULL, $sortBy ASC");
                } else {
                    // 升序：下降最多的优先（最大的正数）
                    $query->orderByRaw("$sortBy IS NULL, $sortBy DESC");
                }
            }

            // 分页查询
            $rankingChanges = $query->paginate(100)->withQueryString();
            
            // 获取统计信息
            $todayCount = RankingChange::whereDate('record_date', $today)->count();
            
            // 计算过滤后的记录数
            $filteredCount = $todayCount;
            if ($filterField && $filterValue !== null && $filterValue !== '') {
                $filterFields = [
                    'current_ranking',
                    'daily_change',
                    'week_change',
                    'biweek_change',
                    'triweek_change',
                    'month_change',
                    'quarter_change'
                    // 移除 year_change
                ];
                
                if (in_array($filterField, $filterFields)) {
                    $filterValue = (int)$filterValue;
                    
                    $countQuery = RankingChange::whereDate('record_date', $today);
                    if ($filterField === 'current_ranking') {
                        $countQuery->where($filterField, '<=', $filterValue);
                    } else {
                        $countQuery->where(function($q) use ($filterField, $filterValue) {
                            $q->where($filterField, '>=', $filterValue)
                              ->orWhere($filterField, '<=', -$filterValue);
                        });
                    }
                    $filteredCount = $countQuery->count();
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
            return redirect()->back()->with('error', '数据加载失败：' . $e->getMessage());
        }
    }
    
    /**
     * 获取最大变化值 - 索引友好版本
     */
    private function getMaxChanges(string $date): array
    {
        $result = [];
        
        $changeFields = [
            'daily_change' => 'max_daily_change',
            'week_change' => 'max_week_change',
            'month_change' => 'max_month_change',
            'quarter_change' => 'max_quarter_change'
        ];
        
        foreach ($changeFields as $field => $resultKey) {
            // 获取最大负值（最大上升）
            $maxUp = DB::table('ranking_changes')
                ->where('record_date', $date)
                ->where($field, '<', 0)
                ->min($field);
            
            // 获取最大正值（最大下降）
            $maxDown = DB::table('ranking_changes')
                ->where('record_date', $date)
                ->where($field, '>', 0)
                ->max($field);
            
            // 比较绝对值
            $maxUpAbs = $maxUp ? abs($maxUp) : 0;
            $maxDownAbs = $maxDown ? abs($maxDown) : 0;
            
            $result[$resultKey] = max($maxUpAbs, $maxDownAbs);
        }
        
        return $result;
    }
    
    /**
     * 获取域名的历史排名变化
     */
    public function getDomainHistory(Request $request, string $domain)
    {
        try {
            $history = RankingChange::where('domain', $domain)
                ->orderBy('record_date', 'desc')
                ->limit(30) // 最近30天
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

            return response()->json([
                'domain' => $domain,
                'history' => $history
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => '获取历史数据失败'], 500);
        }
    }
}