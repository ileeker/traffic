<?php

namespace App\Http\Controllers;

use App\Models\Domain;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DomainController extends Controller
{
    /**
     * 获取指定域名的排名信息并显示页面
     *
     * @param string $domain
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function getRanking(string $domain)
    {
        try {
            // 根据域名查找记录
            $domainRecord = Domain::where('domain', $domain)->first();
            
            // 如果未找到域名记录
            if (!$domainRecord) {
                return redirect()->back()->with('error', '域名 ' . $domain . ' 未找到');
            }
            
            // 返回视图并传递数据
            return view('domain.ranking', compact('domainRecord'));
            
        } catch (\Exception $e) {
            // 处理异常情况
            return redirect()->back()->with('error', '获取域名信息失败：' . $e->getMessage());
        }
    }
}