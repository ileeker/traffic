<?php

namespace App\Http\Controllers;

use App\Models\NewDomainRanking;
use App\Models\MonitoredDomain;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class NewDomainRankingController extends Controller
{
    
    public function index(Request $request)
    {
        // 获取排序参数
        $sortBy = $request->get('sort_by', 'current_ranking');
        $sortOrder = $request->get('sort_order', 'asc');
        
        // 获取分类筛选参数（URL解码处理特殊字符）
        $selectedCategory = $request->get('category');
        if ($selectedCategory) {
            $selectedCategory = urldecode($selectedCategory);
        }
        
        // 获取所有可见记录的分类去重列表
        $categories = NewDomainRanking::where('is_visible', true)
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->pluck('category')
            ->sort()
            ->values();
        
        // 构建查询
        $query = NewDomainRanking::where('is_visible', true);
        
        // 应用分类筛选
        if ($selectedCategory && $selectedCategory !== '') {
            $query->where('category', $selectedCategory);
        }
        
        // 应用排序
        $query->orderBy($sortBy, $sortOrder);
        
        // 分页获取数据
        $rankings = $query->paginate(100)->withQueryString();
        
        // 获取今日记录总数
        $todayCount = NewDomainRanking::where('is_visible', true)
            ->whereDate('created_at', today())
            ->count();
        
        return view('tranco.new', compact(
            'rankings', 
            'sortBy', 
            'sortOrder', 
            'categories',
            'selectedCategory',
            'todayCount'
        ));
    }

    /**
     * 隐藏指定域名（将 is_visible 设置为 false）
     *
     * @param string $domain
     * @return \Illuminate\Http\RedirectResponse
     */
    public function hideDomain($domain)
    {
        try {
            // 查找并更新域名记录
            $updated = NewDomainRanking::where('domain', $domain)
                ->where('is_visible', true)
                ->update(['is_visible' => false]);
            
            if ($updated > 0) {
                return;
                // return redirect()->back()->with('success', "域名 {$domain} 已成功隐藏");
            } else {
                return;
                // return redirect()->back()->with('error', "未找到域名 {$domain} 或该域名已被隐藏");
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', "操作失败：" . $e->getMessage());
        }
    }

    /**
     * 添加域名到监控列表
     *
     * @param string $domain
     * @return JsonResponse
     */
    public function addDomain(string $domain): JsonResponse
    {
        try {
            // 验证域名格式
            $validator = Validator::make(['domain' => $domain], [
                'domain' => 'required|string|max:255|regex:/^([a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z]{2,}$/'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => '域名格式不正确',
                    'errors' => $validator->errors()
                ], 422);
            }

            // 清理域名（去除协议和www前缀）
            $cleanDomain = $this->cleanDomain($domain);

            // 从 NewDomainRanking 获取域名信息
            $domainInfo = $this->getDomainInfoFromNewDomainRanking($cleanDomain);

            // 检查域名是否已存在
            $existingDomain = MonitoredDomain::where('domain', $cleanDomain)->first();

            if ($existingDomain) {
                // 如果已存在，更新 is_visible 为 true，并更新描述和注册时间（如果有新信息）
                $updateData = ['is_visible' => true];
                
                if ($domainInfo['description'] && !$existingDomain->description) {
                    $updateData['description'] = $domainInfo['description'];
                }
                
                if ($domainInfo['registered_at'] && !$existingDomain->registered_at) {
                    $updateData['registered_at'] = $domainInfo['registered_at'];
                }
                
                $existingDomain->update($updateData);
                
                return response()->json([
                    'success' => true,
                    'message' => '域名已存在，已更新为可见状态',
                    'data' => [
                        'id' => $existingDomain->id,
                        'domain' => $existingDomain->domain,
                        'description' => $existingDomain->description,
                        'registered_at' => $existingDomain->registered_at,
                        'is_visible' => $existingDomain->is_visible,
                        'created_at' => $existingDomain->created_at,
                        'updated_at' => $existingDomain->updated_at
                    ]
                ]);
            }

            // 创建新的监控域名
            $monitoredDomain = MonitoredDomain::create([
                'domain' => $cleanDomain,
                'is_visible' => true,
                'description' => $domainInfo['description'],
                'registered_at' => $domainInfo['registered_at']
            ]);

            return response()->json([
                'success' => true,
                'message' => '域名添加成功',
                'data' => [
                    'id' => $monitoredDomain->id,
                    'domain' => $monitoredDomain->domain,
                    'description' => $monitoredDomain->description,
                    'registered_at' => $monitoredDomain->registered_at,
                    'is_visible' => $monitoredDomain->is_visible,
                    'created_at' => $monitoredDomain->created_at,
                    'updated_at' => $monitoredDomain->updated_at
                ]
            ], 201);

        } catch (\Exception $e) {
            Log::error('添加域名失败: ' . $e->getMessage(), [
                'domain' => $domain,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => '添加域名时发生错误，请稍后重试'
            ], 500);
        }
    }

    /**
     * 从 NewDomainRanking 获取域名信息（描述和注册时间）
     *
     * @param string $domain
     * @return array
     */
    private function getDomainInfoFromNewDomainRanking(string $domain): array
    {
        try {
            $newDomainRanking = NewDomainRanking::where('domain', $domain)->first();
            
            $result = [
                'description' => null,
                'registered_at' => null
            ];
            
            if ($newDomainRanking) {
                // 从 metadata 中获取 description_zh
                if ($newDomainRanking->metadata) {
                    $metadata = $newDomainRanking->metadata;
                    $result['description'] = $metadata['description_zh'] ?? null;
                }
                
                // 获取 registered_at
                $result['registered_at'] = $newDomainRanking->registered_at;
            }
            
            return $result;
            
        } catch (\Exception $e) {
            Log::error('获取域名信息失败: ' . $e->getMessage(), [
                'domain' => $domain,
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'description' => null,
                'registered_at' => null
            ];
        }
    }

    /**
     * 清理域名格式
     * 去除协议前缀、www前缀和尾部斜杠
     *
     * @param string $domain
     * @return string
     */
    private function cleanDomain(string $domain): string
    {
        // 去除协议
        $domain = preg_replace('/^https?:\/\//', '', $domain);
        
        // 去除 www 前缀
        $domain = preg_replace('/^www\./', '', $domain);
        
        // 去除尾部斜杠和路径
        $domain = preg_replace('/\/.*$/', '', $domain);
        
        // 转换为小写
        $domain = strtolower(trim($domain));
        
        return $domain;
    }

    /**
     * 从 NewDomainRanking 获取域名描述
     *
     * @param string $domain
     * @return string|null
     */
    private function getDescriptionFromNewDomainRanking(string $domain): ?string
    {
        try {
            $newDomainRanking = NewDomainRanking::where('domain', $domain)->first();
            
            if ($newDomainRanking && $newDomainRanking->metadata) {
                // 从 metadata 中获取 description_zh
                $metadata = $newDomainRanking->metadata;
                return $metadata['description_zh'] ?? null;
            }
            
            return null;
        } catch (\Exception $e) {
            Log::error('获取域名描述失败: ' . $e->getMessage(), [
                'domain' => $domain,
                'trace' => $e->getTraceAsString()
            ]);
            
            return null;
        }
    }
}