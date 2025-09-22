<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\NewDomainRanking;
use Illuminate\Http\Request;

class DomainRankingController extends Controller
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
        $sortField = $request->get('sort', 'current_ranking');
        $sortDirection = $request->get('direction', 'asc');
        
        // 获取搜索参数
        $search = $request->get('search');
        
        // 构建查询
        $query = NewDomainRanking::where('is_visible', true);
        
        // 如果有搜索关键词
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('domain', 'like', '%' . $search . '%')
                  ->orWhere('metadata->category', 'like', '%' . $search . '%')
                  ->orWhere('metadata->description_zh', 'like', '%' . $search . '%');
            });
        }
        
        // 应用排序
        $query->orderBy($sortField, $sortDirection);
        
        // 分页获取数据
        $rankings = $query->paginate(100)->withQueryString();
        
        return view('domain-rankings.index', compact('rankings', 'sortField', 'sortDirection', 'search'));
    }
    
}