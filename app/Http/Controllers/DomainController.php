<?php

namespace App\Http\Controllers;

use App\Models\Domain;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DomainController extends Controller
{
    /**
     * 检索域名信息并在视图中显示其排名历史。
     *
     * @param  string  $domain
     * @return \Illuminate\View\View
     */
    public function getRanking(string $domain): View
    {
        // 查询域名信息，如果找不到则自动返回 404
        $domainInfo = Domain::where('domain', $domain)->firstOrFail();

        // 返回 'domain-ranking' 视图，并将 $domainInfo 数据传递给它
        return view('domain-ranking', [
            'domainInfo' => $domainInfo
        ]);
    }
}
