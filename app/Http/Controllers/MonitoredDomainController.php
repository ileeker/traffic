<?php

namespace App\Http\Controllers;

use App\Models\MonitoredDomain;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MonitoredDomainController extends Controller
{
    /**
     * 获取简化的监控域名数据并返回到视图
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        try {
            $monitoredDomains = MonitoredDomain::with([
                'domainData:domain,current_ranking',
                'similarwebData:domain,current_emv'
            ])
            ->select('domain', 'description', 'is_visible')
            ->paginate(50); // 添加分页支持

            $formattedData = $monitoredDomains->getCollection()->map(function ($monitoredDomain) {
                return [
                    'domain' => $monitoredDomain->domain,
                    'description' => $monitoredDomain->description,
                    'is_visible' => $monitoredDomain->is_visible,
                    'current_ranking' => $monitoredDomain->domainData?->current_ranking ?? null,
                    'current_emv' => $monitoredDomain->similarwebData?->current_emv ?? null,
                ];
            });

            // 替换集合数据
            $monitoredDomains->setCollection($formattedData);

            return view('domain.monitor', [
                'monitoredDomains' => $monitoredDomains,
                'totalCount' => $monitoredDomains->total()
            ]);

        } catch (\Exception $e) {
            return view('domain.monitor', [
                'monitoredDomains' => collect(),
                'totalCount' => 0,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * 获取简化的监控域名数据（仅包含 current_ranking 和 current_emv）
     * 保留 API 接口
     *
     * @return JsonResponse
     */
    public function getSimpleData(): JsonResponse
    {
        try {
            $monitoredDomains = MonitoredDomain::with([
                'domainData:domain,current_ranking',
                'similarwebData:domain,current_emv'
            ])
            ->select('domain', 'description', 'is_visible')
            ->get();

            $formattedData = $monitoredDomains->map(function ($monitoredDomain) {
                return [
                    'domain' => $monitoredDomain->domain,
                    'description' => $monitoredDomain->description,
                    'is_visible' => $monitoredDomain->is_visible,
                    'current_ranking' => $monitoredDomain->domainData?->current_ranking ?? null,
                    'current_emv' => $monitoredDomain->similarwebData?->current_emv ?? null,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $formattedData
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve data',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}