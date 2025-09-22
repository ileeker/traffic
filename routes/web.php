<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DomainController;
use App\Http\Controllers\TrancoChangeController;
use App\Http\Controllers\SimilarwebChangeController;
use App\Http\Controllers\SimilarwebAllController;
use App\Http\Controllers\NewDomainRankingController;
use Illuminate\Support\Facades\Route;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // 详细页面路由
    Route::get('/domain/{domain}', [DomainController::class, 'getDomainDetail'])->name('domain.ranking');
    Route::post('/domains', [DomainController::class, 'getDomainsDetail'])->name('domains.detail');
    
    // Similarweb域名数据浏览路由
    Route::get('/similarweb-all-domain', [SimilarwebAllController::class, 'index'])->name('domains.browse');
    
    // Similarweb域名分类统计页面
    Route::get('/similarweb-categories', [SimilarwebAllController::class, 'showCategories'])->name('domains.categories');
    
    // Similarweb显示指定分类下的所有域名
    Route::get('/similarweb-categories/{category}', [SimilarwebAllController::class, 'showCategoryDomains'])->name('domains.category.domains');
    
    // 排名变化相关路由
    // Route::prefix('ranking-changes')->name('ranking-changes.')->group(function () {
    //     // 排名变化列表页面
    //     Route::get('/', [RankingChangeController::class, 'index'])->name('index');
    // });
    Route::get('/tranco-ranking-change', [TrancoChangeController::class, 'index'])->name('ranking-changes.index');

    // Similarweb EMV变化相关路由
    Route::get('/similarweb-ranking-change', [SimilarwebChangeController::class, 'index'])->name('similarweb-changes.index');

    // 新注册域名的排名路由
    Route::get('/new-domain-ranking', [NewDomainRankingController::class, 'index'])->name('new.domain.ranking');

});

require __DIR__.'/auth.php';