<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DomainController;
use App\Http\Controllers\RankingChangeController;
use App\Http\Controllers\SimilarwebChangeController;
use App\Http\Controllers\DomainRankingController;
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
    
    // 域名数据浏览路由
    Route::get('/domains/browse', [DomainController::class, 'browseDomains'])->name('domains.browse');
    
    // 新增：域名分类统计页面
    Route::get('/domains/categories', [DomainController::class, 'showCategories'])->name('domains.categories');
    
    // 显示指定分类下的所有域名
    Route::get('/domains/categories/{category}/domains', [DomainController::class, 'showCategoryDomains'])->name('domains.category.domains');
    
    // 排名变化相关路由
    Route::prefix('ranking-changes')->name('ranking-changes.')->group(function () {
        // 排名变化列表页面
        Route::get('/', [RankingChangeController::class, 'index'])->name('index');
    });

    // Similarweb EMV变化相关路由
    Route::prefix('similarweb-changes')->name('similarweb-changes.')->group(function () {
        // EMV变化列表页面
        Route::get('/', [SimilarwebChangeController::class, 'index'])->name('index');
    });

    // 新注册域名的排名路由
    Route::get('/new-domain-ranking', [NewDomainRankingController::class, 'index'])->name('new.domain.ranking');
 
    Route::prefix('domain-rankings')->name('domain-rankings.')->group(function () {
        Route::get('/', [NewDomainRankingController::class, 'index'])->name('index');
        Route::get('/{id}', [NewDomainRankingController::class, 'show'])->name('show');
    });


});

require __DIR__.'/auth.php';