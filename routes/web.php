<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DomainController;
use App\Http\Controllers\RankingChangeController;
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
    Route::get('/domain/{domain}', [DomainController::class, 'getRanking'])->name('domain.ranking');
    Route::get('/test/{domain}', [DomainController::class, 'getTest'])->name('domain.test');
    Route::post('/domains', [DomainController::class, 'getDomainsDetail'])->name('domains.detail');
    Route::get('/domains', [DomainController::class, 'getDomainsRanking'])->name('domains.ranking');

    // 域名数据浏览路由
    Route::get('/domains/browse', [DomainController::class, 'browseDomains'])->name('domains.browse');

    // 排名变化相关路由
    Route::prefix('ranking-changes')->name('ranking-changes.')->group(function () {
        // 排名变化列表页面
        Route::get('/', [RankingChangeController::class, 'index'])->name('index');
        
        // 获取统计信息API
        Route::get('/stats', [RankingChangeController::class, 'getStats'])->name('stats');
    });
});

require __DIR__.'/auth.php';