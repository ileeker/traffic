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
        $rankings = $query->paginate(20)->withQueryString();
        
        return view('domain-rankings.index', compact('rankings', 'sortField', 'sortDirection', 'search'));
    }
    
    /**
     * 显示单个域名详情
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $ranking = NewDomainRanking::findOrFail($id);
        
        // 确保域名是可见的
        if (!$ranking->is_visible) {
            abort(404);
        }
        
        return view('domain-rankings.show', compact('ranking'));
    }
    
    /**
     * 获取趋势图标HTML
     *
     * @param string|null $trend
     * @return string
     */
    public static function getTrendIcon($trend)
    {
        switch ($trend) {
            case 'up':
                return '<span class="text-success">↑</span>';
            case 'down':
                return '<span class="text-danger">↓</span>';
            case 'stable':
                return '<span class="text-secondary">→</span>';
            default:
                return '<span class="text-muted">-</span>';
        }
    }
    
    /**
     * 格式化变化数值
     *
     * @param int|null $change
     * @param string|null $trend
     * @return string
     */
    public static function formatChange($change, $trend)
    {
        if ($change === null) {
            return '-';
        }
        
        $prefix = '';
        $class = '';
        
        switch ($trend) {
            case 'up':
                $prefix = '+';
                $class = 'text-success';
                break;
            case 'down':
                $prefix = '-';
                $class = 'text-danger';
                break;
            case 'stable':
                $class = 'text-secondary';
                break;
        }
        
        return sprintf('<span class="%s">%s%s</span>', $class, $prefix, abs($change));
    }
}