<?php

namespace App\Http\Controllers;

use App\Models\Domain; // 引入 Domain 模型
use Illuminate\Http\Request;

class DomainController extends Controller
{
    /**
     * 根据域名检索并返回其所有信息。
     *
     * @param  string  $domain  // 从路由中接收域名参数
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRanking(string $domain)
    {
        // 使用 where() 方法来根据 'domain' 字段查询
        // 使用 firstOrFail() 方法，如果找不到对应的记录，会自动抛出异常并返回 404 响应
        $domainInfo = Domain::where('domain', $domain)->firstOrFail();

        // 如果找到了记录，Laravel 会自动将其转换为 JSON 格式返回
        return response()->json($domainInfo);
    }
}