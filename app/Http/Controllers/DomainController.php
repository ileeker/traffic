<?php

namespace App\Http\Controllers;

use App\Models\Domain;
use App\Models\SimilarwebDomain;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DomainController extends Controller
{
    /**
     * 获取指定域名的排名信息和 Similarweb 数据并显示页面
     *
     * @param string $domain
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function getDomainDetail(string $domain)
    {
        try {
            // 根据域名查找排名记录
            $domainRecord = Domain::with('websiteIntroduction')->where('domain', $domain)->first();
            
            // 根据域名查找 SimilarwebDomain 记录
            $similarwebRecord = SimilarwebDomain::where('domain', $domain)->first();
            
            // 如果两个记录都未找到
            if (!$domainRecord && !$similarwebRecord) {
                return redirect()->back()->with('error', '域名 ' . $domain . ' 未找到任何记录');
            }
            
            // 获取 websiteIntroduction（从任一个存在的记录中获取）
            $websiteIntroduction = optional($domainRecord)->websiteIntroduction;

            // 返回视图并传递数据
            return view('domain.ranking', compact('domainRecord', 'similarwebRecord', 'websiteIntroduction'));
            
        } catch (\Exception $e) {
            // 处理异常情况
            return redirect()->back()->with('error', '获取域名信息失败：' . $e->getMessage());
        }
    }

    /**
     * 批量获取域名的 Similarweb 数据
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function getDomainsDetail(Request $request)
    {
        try {
            // 验证请求数据
            $request->validate([
                'domains' => 'required|string|max:10000'
            ]);

            // 获取域名列表，按行分割并清理
            $domainLines = explode("\n", $request->input('domains'));
            $domains = array_filter(array_map('trim', $domainLines));
            
            if (empty($domains)) {
                return redirect()->back()->with('error', '请输入有效的域名列表');
            }

            // 限制查询数量
            if (count($domains) > 100) {
                return redirect()->back()->with('error', '一次最多只能查询 100 个域名');
            }

            // 批量查询 SimilarwebDomain 数据，并加载 websiteIntroduction 关联
            $similarwebRecords = SimilarwebDomain::with('websiteIntroduction')
                ->whereIn('domain', $domains)
                ->select([
                    'domain',
                    'current_month', 
                    'current_emv',
                    'ts_social',
                    'ts_paid_referrals', 
                    'ts_mail',
                    'ts_referrals',
                    'ts_search',
                    'ts_direct',
                    'global_rank',
                    'last_updated'
                ])
                ->orderByRaw('FIELD(domain, "' . implode('","', $domains) . '")')
                ->get()
                ->keyBy('domain');

            // 准备结果数据，保持输入顺序
            $results = [];
            $foundDomains = [];
            $notFoundDomains = [];

            foreach ($domains as $domain) {
                if ($similarwebRecords->has($domain)) {
                    $record = $similarwebRecords[$domain];
                    $results[] = [
                        'domain' => $record->domain,
                        'current_month' => $record->current_month,
                        'current_emv' => $record->current_emv,
                        'global_rank' => $record->global_rank,
                        'traffic_sources' => [
                            'direct' => $record->ts_direct,
                            'search' => $record->ts_search,
                            'referrals' => $record->ts_referrals,
                            'social' => $record->ts_social,
                            'paid' => $record->ts_paid_referrals,
                            'mail' => $record->ts_mail
                        ],
                        'last_updated' => $record->last_updated,
                        // 添加 websiteIntroduction 数据，处理可能为 null 的情况
                        'registered_at' => optional($record->websiteIntroduction)->registered_at
                    ];
                    $foundDomains[] = $domain;
                } else {
                    $notFoundDomains[] = $domain;
                }
            }

            return view('domain.detail', compact(
                'results',
                'foundDomains', 
                'notFoundDomains',
                'domains'
            ));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', '查询失败：' . $e->getMessage());
        }
    }

}