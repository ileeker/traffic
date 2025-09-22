<?php

namespace App\Http\Controllers;

use App\Models\RankingChange;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class TrancoChangeController extends Controller
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

            // 判断是否需要 JOIN (参考 SimilarwebChangeController 的逻辑)
            $needsJoin = ($sortBy === 'registered_at') || 
                        ($filterField === 'registered_after');

            if ($needsJoin) {
                // 使用 DB 查询构建器，类似 SimilarwebChangeController
                $query = DB::table('ranking_changes')
                    ->leftJoin('website_introductions', 'ranking_changes.domain', '=', 'website_introductions.domain')
                    ->where('ranking_changes.record_date', $today)
                    ->select(
                        'ranking_changes.*',
                        'website_introductions.registered_at'
                    );
            } else {
                // 使用 Eloquent 查询以获得更好的性能
                $query = RankingChange::where('record_date', $today);
            }

            // 应用筛选条件
            if ($filterField && $filterValue !== null && $filterValue !== '') {
                if ($filterField === 'registered_after') {
                    try {
                        $filterDate = Carbon::parse($filterValue)->toDateString();
                        if ($needsJoin) {
                            $query->whereDate('website_introductions.registered_at', '>=', $filterDate);
                        } else {
                            // 如果筛选注册日期但之前没有 JOIN，现在需要添加 JOIN
                            $needsJoin = true;
                            $query = DB::table('ranking_changes')
                                ->leftJoin('website_introductions', 'ranking_changes.domain', '=', 'website_introductions.domain')
                                ->where('ranking_changes.record_date', $today)
                                ->whereDate('website_introductions.registered_at', '>=', $filterDate)
                                ->select(
                                    'ranking_changes.*',
                                    'website_introductions.registered_at'
                                );
                        }
                    } catch (\Exception $e) {
                        // 日期格式错误，忽略此筛选器
                    }
                } elseif ($filterField === 'current_ranking') {
                    $filterValue = (int)$filterValue;
                    $fieldName = $needsJoin ? 'ranking_changes.current_ranking' : 'current_ranking';
                    $query->where($fieldName, '<=', $filterValue);
                } elseif (in_array($filterField, ['daily_change', 'week_change', 'biweek_change', 'triweek_change', 'month_change', 'quarter_change'])) {
                    $filterValue = (int)$filterValue;
                    if ($filterValue > 0) {
                        $fieldName = $needsJoin ? "ranking_changes.{$filterField}" : $filterField;
                        $query->where(function($q) use ($fieldName, $filterValue) {
                            $q->whereBetween($fieldName, [-999999, -$filterValue])
                              ->orWhereBetween($fieldName, [$filterValue, 999999]);
                        });
                    }
                }
            }

            // 应用排序
            if ($sortBy === 'registered_at') {
                // 处理注册时间排序，NULL 值放在最后
                $nullValue = $sortOrder === 'asc' ? '9999-12-31' : '1000-01-01';
                $query->orderByRaw("COALESCE(website_introductions.registered_at, '{$nullValue}') {$sortOrder}");
            } elseif (in_array($sortBy, ['daily_change', 'week_change', 'biweek_change', 'triweek_change', 'month_change', 'quarter_change'])) {
                // 变化字段排序优化
                $fieldName = $needsJoin ? "ranking_changes.{$sortBy}" : $sortBy;
                if ($sortOrder === 'desc') {
                    // 上升最多的优先（负数）
                    $query->orderByRaw("CASE WHEN {$fieldName} < 0 THEN 0 ELSE 1 END, {$fieldName} ASC");
                } else {
                    // 下降最多的优先（正数）
                    $query->orderByRaw("CASE WHEN {$fieldName} > 0 THEN 0 ELSE 1 END, {$fieldName} DESC");
                }
            } else {
                $fieldName = $needsJoin ? "ranking_changes.{$sortBy}" : $sortBy;
                $query->orderBy($fieldName, $sortOrder);
            }

            // 分页查询
            if ($needsJoin) {
                $rankingChanges = $query->paginate(100);
                
                // 手动加载关联关系 (因为 DB 查询不会自动创建 Eloquent 模型)
                $domains = collect($rankingChanges->items())->pluck('domain')->toArray();
                $websiteIntroductions = \App\Models\WebsiteIntroduction::whereIn('domain', $domains)
                    ->select('domain', 'intro', 'registered_at', 'archived_at')
                    ->get()
                    ->keyBy('domain');
                
                // 将关联数据附加到结果中
                foreach ($rankingChanges->items() as $item) {
                    $item->websiteIntroduction = $websiteIntroductions->get($item->domain);
                }
                
                $rankingChanges->withQueryString();
            } else {
                // 使用 Eloquent 查询时的分页，包含关联加载
                $rankingChanges = $query->with(['websiteIntroduction:domain,intro,registered_at,archived_at'])
                    ->paginate(100)
                    ->withQueryString();
            }

            // 缓存今日记录总数
            $cacheKey = "ranking_count_{$today}";
            $todayCount = Cache::remember($cacheKey, 300, function() use ($today) {
                return DB::table('ranking_changes')
                    ->where('record_date', $today)
                    ->count();
            });

            // 计算过滤后的记录数
            $filteredCount = $rankingChanges->total();

            return view('tranco.change', compact(
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

}