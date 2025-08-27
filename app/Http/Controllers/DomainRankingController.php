<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Domain;
use App\Models\SimilarwebDomain;
use Illuminate\Http\JsonResponse;

class DomainRankingController extends Controller
{
    /**
     * 获取域名排名数据
     *
     * @param string|null $domain
     * @return JsonResponse|\Illuminate\View\View
     */
    public function getDomainRanking(string $domain = null)
    {
        try {
            // 优先从路由参数获取domain，如果没有则从查询参数获取
            $domainName = $domain ?: request()->get('domain');
            
            if (!$domainName) {
                if (request()->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Domain parameter is required'
                    ], 400);
                }
                
                return redirect()->back()->withErrors(['error' => 'Domain parameter is required']);
            }
            
            // 从Domain表获取对应域名的所有信息
            $domainData = Domain::where('name', $domainName)->first();
            
            if (!$domainData) {
                if (request()->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Domain not found'
                    ], 404);
                }
                
                return redirect()->back()->withErrors(['error' => 'Domain not found']);
            }
            
            // 解析ranking_data JSON数据
            $rankingData = null;
            if ($domainData->ranking_data) {
                $rankingData = json_decode($domainData->ranking_data, true);
            }
            
            // 根据请求类型返回数据
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'domain' => $domainData,
                    'ranking_data' => $rankingData
                ]);
            }
            
            // 返回视图，传递完整的domain数据和解析后的ranking_data
            return view('ranking.domain', [
                'domain' => $domainData,
                'rankingData' => $rankingData
            ]);
            
        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to fetch domain ranking data',
                    'error' => $e->getMessage()
                ], 500);
            }
            
            return back()->withErrors(['error' => 'Failed to fetch domain ranking data: ' . $e->getMessage()]);
        }
    }
    
    /**
     * 合并两个数据源的数据
     *
     * @param $domainData
     * @param $similarwebData
     * @return array
     */
    private function mergeRankingData($domainData, $similarwebData): array
    {
        $result = [
            'domain' => null,
            'basic_info' => [],
            'ranking_info' => [],
            'traffic_info' => []
        ];
        
        // 处理Domain表数据
        if ($domainData) {
            $result['domain'] = $domainData->name;
            $result['basic_info'] = [
                'id' => $domainData->id,
                'name' => $domainData->name,
                'status' => $domainData->status ?? null,
                'category' => $domainData->category ?? null,
                'created_at' => $domainData->created_at,
                // 添加其他需要的字段
            ];
        }
        
        // 处理SimilarwebDomain表数据
        if ($similarwebData) {
            $result['ranking_info'] = [
                'global_rank' => $similarwebData->global_rank ?? null,
                'country_rank' => $similarwebData->country_rank ?? null,
                'category_rank' => $similarwebData->category_rank ?? null,
                'rank_change' => $similarwebData->rank_change ?? null,
            ];
            
            $result['traffic_info'] = [
                'monthly_visits' => $similarwebData->monthly_visits ?? null,
                'bounce_rate' => $similarwebData->bounce_rate ?? null,
                'pages_per_visit' => $similarwebData->pages_per_visit ?? null,
                'avg_visit_duration' => $similarwebData->avg_visit_duration ?? null,
                // 添加其他流量相关字段
            ];
        }
        
        return $result;
    }
    
    /**
     * 获取域名的历史排名趋势
     *
     * @param string $domain
     * @return JsonResponse
     */
    public function getDomainTrend(string $domain): JsonResponse
    {
        try {
            // 获取历史数据（假设有时间字段）
            $trendData = SimilarwebDomain::where('domain', $domain)
                ->orderBy('updated_at', 'desc')
                ->limit(12) // 最近12个月
                ->get(['global_rank', 'monthly_visits', 'updated_at']);
            
            return response()->json([
                'success' => true,
                'data' => $trendData
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch domain trend data',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}