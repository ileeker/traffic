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
        
        // 获取筛选参数
        $filterField = $request->get('filter_field');
        $filterValue = $request->get('filter_value');
        
        // 构建查询
        $query = NewDomainRanking::where('is_visible', true);
        
        // 应用筛选
        if ($filterField && $filterValue !== null && $filterValue !== '') {
            switch ($filterField) {
                case 'category':
                    $query->where('category', 'like', '%' . $filterValue . '%');
                    break;
                case 'current_ranking':
                    $query->where('current_ranking', '<=', (int)$filterValue);
                    break;
                case 'daily_change':
                    $query->where('daily_change', '>=', (int)$filterValue);
                    break;
                case 'week_change':
                    $query->where('week_change', '>=', (int)$filterValue);
                    break;
                case 'biweek_change':
                    $query->where('biweek_change', '>=', (int)$filterValue);
                    break;
                case 'triweek_change':
                    $query->where('triweek_change', '>=', (int)$filterValue);
                    break;
                case 'registered_after':
                    $query->where('registered_at', '>=', $filterValue);
                    break;
            }
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
            'filterField', 
            'filterValue',
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