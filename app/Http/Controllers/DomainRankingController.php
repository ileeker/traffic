<?php

namespace App\Http\Controllers;

use App\Models\NewDomainRanking;
use Illuminate\Http\Request;

class DomainRankingController extends Controller
{
    /**
     * 显示 is_visible=true 的域名排名列表
     */
    public function index(Request $request)
    {
        // 每页数量可通过 ?per_page= 参数控制，默认 50
        $perPage = (int) $request->input('per_page', 50);
        $perPage = $perPage > 0 && $perPage <= 200 ? $perPage : 50;

        // 只取列表需要的字段，避免不必要的 IO
        $domains = NewDomainRanking::query()
            ->where('is_visible', true)
            ->orderBy('rank')                     // 如需其它排序可改
            ->select(['id','domain','rank','metadata'])
            ->paginate($perPage)
            ->withQueryString();

        return view('domains.index', compact('domains'));
    }
}
