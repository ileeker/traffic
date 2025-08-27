<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Domain;
use Illuminate\Http\JsonResponse;

class DomainController extends Controller
{
    /**
     * 处理域名搜索请求（接受查询参数）
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function getRanking(Request $request)
    {
        return "Hello";
    }
    
    /**
     * 获取域名排名数据（接受路由参数）
     *
     * @param string $domain
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function getDomainRanking($domain)
    {
        try {
            // 从Domain表获取对应域名的所有信息
            $domainData = Domain::where('name', $domain)->first();
            
            if (!$domainData) {
                return redirect()->back()->withErrors(['error' => "Domain '{$domain}' not found in database"]);
            }
            
            // 解析ranking_data JSON数据
            $rankingData = null;
            if ($domainData->ranking_data) {
                $rankingData = json_decode($domainData->ranking_data, true);
                
                // 检查JSON解析是否成功
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $rankingData = null;
                }
            }
            
            // 返回视图
            return view('ranking.domain', [
                'domain' => $domainData,
                'rankingData' => $rankingData
            ]);
            
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to fetch domain ranking data: ' . $e->getMessage()]);
        }
    }
    
    /**
     * API接口：获取域名排名数据
     *
     * @param string $domain
     * @return JsonResponse
     */
    public function getDomainRankingApi($domain)
    {
        try {
            $domainData = Domain::where('name', $domain)->first();
            
            if (!$domainData) {
                return response()->json([
                    'success' => false,
                    'message' => 'Domain not found'
                ], 404);
            }
            
            $rankingData = null;
            if ($domainData->ranking_data) {
                $rankingData = json_decode($domainData->ranking_data, true);
            }
            
            return response()->json([
                'success' => true,
                'domain' => $domainData,
                'ranking_data' => $rankingData
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch domain ranking data',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}