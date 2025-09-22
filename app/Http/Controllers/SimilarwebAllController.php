<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SimilarwebDomain;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class SimilarwebAllController extends Controller
{
    /**
     * 浏览域名数据 - 分页展示上个月数据
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        try {
            $currentMonth = SimilarwebDomain::find(1)->current_month;
            $lastMonth = $currentMonth;
            
            // 获取排序参数
            $sortBy = $request->get('sort', 'current_emv');
            $sortOrder = $request->get('order', 'desc');
            
            // 获取过滤参数
            $filterField = $request->get('filter_field');
            $filterValue = $request->get('filter_value');
            
            // 验证排序字段
            $allowedSorts = [
                'current_emv',
                'ts_direct',
                'ts_search',
                'ts_referrals', 
                'ts_social',
                'ts_paid_referrals',
                'ts_mail',
                'registered_at'
            ];
            
            if (!in_array($sortBy, $allowedSorts)) {
                $sortBy = 'current_emv';
            }
            
            // 验证排序顺序
            if (!in_array($sortOrder, ['asc', 'desc'])) {
                $sortOrder = 'desc';
            }

            // 判断是否需要 JOIN (参考 SimilarwebChangeController 的逻辑)
            $needsJoin = ($sortBy === 'registered_at') || ($filterField === 'registered_at');

            // 构建查询
            if ($needsJoin) {
                // 需要注册时间排序或过滤时，使用 JOIN 查询
                $query = DB::table('similarweb_domains')
                    ->leftJoin('website_introductions', 'similarweb_domains.domain', '=', 'website_introductions.domain')
                    ->where('similarweb_domains.current_month', $lastMonth)
                    ->select(
                        'similarweb_domains.domain',
                        'similarweb_domains.current_emv',
                        'similarweb_domains.ts_direct',
                        'similarweb_domains.ts_search',
                        'similarweb_domains.ts_referrals',
                        'similarweb_domains.ts_social',
                        'similarweb_domains.ts_paid_referrals',
                        'similarweb_domains.ts_mail',
                        'website_introductions.registered_at'
                    );
            } else {
                // 不需要时，使用 Eloquent 查询以获得更好的性能
                $query = SimilarwebDomain::where('current_month', $lastMonth);
            }

            // 应用过滤条件
            if ($filterField && $filterValue !== null && $filterValue !== '') {
                if ($filterField === 'registered_at') {
                    // 处理日期过滤
                    try {
                        $dateValue = \Carbon\Carbon::parse($filterValue)->format('Y-m-d');
                        if ($needsJoin) {
                            $query->whereDate('website_introductions.registered_at', '>=', $dateValue);
                        } else {
                            // 如果之前没有 JOIN，现在需要添加
                            $needsJoin = true;
                            $query = DB::table('similarweb_domains')
                                ->leftJoin('website_introductions', 'similarweb_domains.domain', '=', 'website_introductions.domain')
                                ->where('similarweb_domains.current_month', $lastMonth)
                                ->whereDate('website_introductions.registered_at', '>=', $dateValue)
                                ->select(
                                    'similarweb_domains.domain',
                                    'similarweb_domains.current_emv',
                                    'similarweb_domains.ts_direct',
                                    'similarweb_domains.ts_search',
                                    'similarweb_domains.ts_referrals',
                                    'similarweb_domains.ts_social',
                                    'similarweb_domains.ts_paid_referrals',
                                    'similarweb_domains.ts_mail',
                                    'website_introductions.registered_at'
                                );
                        }
                    } catch (\Exception $e) {
                        // 忽略无效的日期格式
                    }
                } elseif (in_array($filterField, ['ts_direct', 'ts_search', 'ts_referrals', 'ts_social', 'ts_paid_referrals', 'ts_mail'])) {
                    // 流量来源字段（百分比）
                    $value = floatval($filterValue) / 100;
                    $fieldName = $needsJoin ? "similarweb_domains.{$filterField}" : $filterField;
                    $query->where($fieldName, '>=', $value);
                } elseif ($filterField === 'current_emv') {
                    // EMV 字段
                    $value = floatval($filterValue);
                    $fieldName = $needsJoin ? 'similarweb_domains.current_emv' : 'current_emv';
                    $query->where($fieldName, '>=', $value);
                }
            }
            
            // 获取统计信息 - 在应用排序之前
            $totalCount = SimilarwebDomain::where('current_month', $lastMonth)->count();
            
            // 获取过滤后的统计信息
            if ($needsJoin) {
                // 对于 JOIN 查询，需要使用 clone 来避免影响主查询
                $countQuery = clone $query;
                $filteredCount = $countQuery->count();
            } else {
                // 对于 Eloquent 查询
                $filteredCount = $query->count();
            }

            // 应用排序
            if ($sortBy === 'registered_at') {
                // 处理注册时间排序，NULL 值放在最后
                $nullValue = $sortOrder === 'asc' ? '9999-12-31' : '1000-01-01';
                $query->orderByRaw("COALESCE(website_introductions.registered_at, '{$nullValue}') {$sortOrder}");
            } else {
                // 其他字段排序
                $fieldName = $needsJoin ? "similarweb_domains.{$sortBy}" : $sortBy;
                $query->orderBy($fieldName, $sortOrder);
            }

            // 分页查询
            if ($needsJoin) {
                $domains = $query->paginate(100);
                
                // 手动加载关联关系 (因为 DB 查询不会自动创建 Eloquent 模型)
                $domainNames = collect($domains->items())->pluck('domain')->toArray();
                $websiteIntroductions = \App\Models\WebsiteIntroduction::whereIn('domain', $domainNames)
                    ->select('domain', 'intro', 'registered_at', 'archived_at')
                    ->get()
                    ->keyBy('domain');
                
                // 将关联数据附加到结果中
                foreach ($domains->items() as $item) {
                    $item->websiteIntroduction = $websiteIntroductions->get($item->domain);
                }
                
                $domains->withQueryString();
            } else {
                // 使用 Eloquent 查询时的分页，包含关联加载
                $domains = $query->with(['websiteIntroduction:domain,intro,registered_at,archived_at'])
                    ->select([
                        'domain', 'current_emv', 'ts_direct', 'ts_search',
                        'ts_referrals', 'ts_social', 'ts_paid_referrals', 'ts_mail'
                    ])
                    ->paginate(100)
                    ->withQueryString();
            }
            
            return view('domains.browse', compact(
                'domains',
                'lastMonth', 
                'sortBy',
                'sortOrder',
                'totalCount',
                'filteredCount',
                'filterField',
                'filterValue'
            ));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', '数据加载失败：' . $e->getMessage());
        }
    }
}
