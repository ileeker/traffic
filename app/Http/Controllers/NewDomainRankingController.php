<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NewDomainRanking;

class NewDomainRankingController extends Controller
{
    /**
     * 显示域名排名列表
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
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
                return redirect()->back()->with('success', "域名 {$domain} 已成功隐藏");
            } else {
                return redirect()->back()->with('error', "未找到域名 {$domain} 或该域名已被隐藏");
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', "操作失败：" . $e->getMessage());
        }
    }
}