<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                今日排名变化数据 - {{ today()->format('Y-m-d') }}
            </h2>
            <div class="text-sm text-gray-600 dark:text-gray-400">
                今日记录 {{ number_format($todayCount) }} 条
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- 新增：域名访问测试按钮 -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-2">域名访问性测试</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">点击按钮测试当前页面所有域名的HTTP/HTTPS访问性</p>
                        </div>
                        <div class="flex items-center space-x-4">
                            <button id="testAllDomains" 
                                    class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors duration-200 flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                测试所有域名
                            </button>
                            <button id="stopTest" 
                                    class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors duration-200 hidden">
                                停止测试
                            </button>
                            <button id="clearResults" 
                                    class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition-colors duration-200 hidden">
                                清除结果
                            </button>
                        </div>
                    </div>
                    <div id="testProgress" class="mt-4 hidden">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm text-gray-600 dark:text-gray-400">测试进度：<span id="progressText">0/0</span></span>
                            <span class="text-sm text-gray-600 dark:text-gray-400">
                                成功: <span id="successCount" class="text-green-600 font-bold">0</span> | 
                                失败: <span id="failCount" class="text-red-600 font-bold">0</span> | 
                                超时: <span id="timeoutCount" class="text-yellow-600 font-bold">0</span>
                            </span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                            <div id="progressBar" class="bg-green-600 h-2.5 rounded-full transition-all duration-300" style="width: 0%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 统计信息和控制面板 -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex flex-wrap justify-between items-center space-y-4 md:space-y-0">
                        <!-- 统计信息 -->
                        <div class="flex items-center space-x-6">
                            <!-- 当前页面 -->
                            <div class="flex items-center">
                                <div class="p-2 bg-blue-500 bg-opacity-10 rounded-full mr-3">
                                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-gray-600 dark:text-gray-400">当前页面</p>
                                    <p class="text-lg font-bold text-gray-900 dark:text-white">
                                        第 {{ $rankingChanges->currentPage() }} 页 / 共 {{ $rankingChanges->lastPage() }} 页
                                    </p>
                                </div>
                            </div>

                            <!-- 显示范围 -->
                            <div class="flex items-center">
                                <div class="p-2 bg-green-500 bg-opacity-10 rounded-full mr-3">
                                    <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-gray-600 dark:text-gray-400">显示范围</p>
                                    <p class="text-lg font-bold text-gray-900 dark:text-white">
                                        {{ $rankingChanges->firstItem() ?? 0 }} - {{ $rankingChanges->lastItem() ?? 0 }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- 控制面板 -->
                        <div class="flex items-center space-x-4">
                            <!-- 页码跳转 -->
                            <div class="flex items-center space-x-2">
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">跳转：</label>
                                <input type="number" 
                                       id="pageJumpInput"
                                       placeholder="页码"
                                       value="{{ $rankingChanges->currentPage() }}"
                                       min="1"
                                       max="{{ $rankingChanges->lastPage() }}"
                                       class="w-16 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                                <button id="pageJumpBtn" 
                                        class="px-3 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition-colors duration-200 text-sm">
                                    GO
                                </button>
                            </div>
                            
                            <!-- 数值过滤器 -->
                            <div class="flex items-center space-x-2">
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">过滤：</label>
                                <select id="filterField" 
                                        class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                                    <option value="">无过滤</option>
                                    <option value="current_ranking" {{ $filterField == 'current_ranking' ? 'selected' : '' }}>排名 ≤</option>
                                    <option value="daily_change" {{ $filterField == 'daily_change' ? 'selected' : '' }}>日变化 ≥</option>
                                    <option value="week_change" {{ $filterField == 'week_change' ? 'selected' : '' }}>周变化 ≥</option>
                                    <option value="biweek_change" {{ $filterField == 'biweek_change' ? 'selected' : '' }}>双周变化 ≥</option>
                                    <option value="triweek_change" {{ $filterField == 'triweek_change' ? 'selected' : '' }}>三周变化 ≥</option>
                                    <option value="month_change" {{ $filterField == 'month_change' ? 'selected' : '' }}>月变化 ≥</option>
                                    <option value="quarter_change" {{ $filterField == 'quarter_change' ? 'selected' : '' }}>季度变化 ≥</option>
                                    <option value="year_change" {{ $filterField == 'year_change' ? 'selected' : '' }}>年变化 ≥</option>
                                </select>
                                <input type="number" 
                                       id="filterValue"
                                       placeholder="数值"
                                       value="{{ $filterValue }}"
                                       class="w-20 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                                <button id="applyFilter" 
                                        class="px-3 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors duration-200 text-sm">
                                    应用
                                </button>
                                @if($filterField)
                                <button id="clearFilter" 
                                        class="px-3 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-colors duration-200 text-sm">
                                    清除
                                </button>
                                @endif
                            </div>

                            <!-- 排序控制 -->
                            <div class="flex items-center space-x-2">
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">排序：</label>
                                <select id="sortSelect" 
                                        class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                                    <option value="current_ranking-asc" {{ $sortBy == 'current_ranking' && $sortOrder == 'asc' ? 'selected' : '' }}>排名 (1→100)</option>
                                    <option value="current_ranking-desc" {{ $sortBy == 'current_ranking' && $sortOrder == 'desc' ? 'selected' : '' }}>排名 (100→1)</option>
                                    <option value="domain-asc" {{ $sortBy == 'domain' && $sortOrder == 'asc' ? 'selected' : '' }}>域名 (A→Z)</option>
                                    <option value="domain-desc" {{ $sortBy == 'domain' && $sortOrder == 'desc' ? 'selected' : '' }}>域名 (Z→A)</option>
                                    <option value="daily_change-desc" {{ $sortBy == 'daily_change' && $sortOrder == 'desc' ? 'selected' : '' }}>日上升最多</option>
                                    <option value="daily_change-asc" {{ $sortBy == 'daily_change' && $sortOrder == 'asc' ? 'selected' : '' }}>日下降最多</option>
                                    <option value="week_change-desc" {{ $sortBy == 'week_change' && $sortOrder == 'desc' ? 'selected' : '' }}>周上升最多</option>
                                    <option value="week_change-asc" {{ $sortBy == 'week_change' && $sortOrder == 'asc' ? 'selected' : '' }}>周下降最多</option>
                                    <option value="biweek_change-desc" {{ $sortBy == 'biweek_change' && $sortOrder == 'desc' ? 'selected' : '' }}>双周上升最多</option>
                                    <option value="biweek_change-asc" {{ $sortBy == 'biweek_change' && $sortOrder == 'asc' ? 'selected' : '' }}>双周下降最多</option>
                                    <option value="triweek_change-desc" {{ $sortBy == 'triweek_change' && $sortOrder == 'desc' ? 'selected' : '' }}>三周上升最多</option>
                                    <option value="triweek_change-asc" {{ $sortBy == 'triweek_change' && $sortOrder == 'asc' ? 'selected' : '' }}>三周下降最多</option>
                                    <option value="month_change-desc" {{ $sortBy == 'month_change' && $sortOrder == 'desc' ? 'selected' : '' }}>月上升最多</option>
                                    <option value="month_change-asc" {{ $sortBy == 'month_change' && $sortOrder == 'asc' ? 'selected' : '' }}>月下降最多</option>
                                    <option value="quarter_change-desc" {{ $sortBy == 'quarter_change' && $sortOrder == 'desc' ? 'selected' : '' }}>季度上升最多</option>
                                    <option value="quarter_change-asc" {{ $sortBy == 'quarter_change' && $sortOrder == 'asc' ? 'selected' : '' }}>季度下降最多</option>
                                    <option value="year_change-desc" {{ $sortBy == 'year_change' && $sortOrder == 'desc' ? 'selected' : '' }}>年上升最多</option>
                                    <option value="year_change-asc" {{ $sortBy == 'year_change' && $sortOrder == 'asc' ? 'selected' : '' }}>年下降最多</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 数据表格 -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        域名
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        当前排名
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        日变化
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        周变化
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        双周变化
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        三周变化
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        月变化
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        季度变化
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        年变化
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($rankingChanges as $change)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <img src="https://www.google.com/s2/favicons?domain={{ $change->domain }}" 
                                                 alt="{{ $change->domain }}" 
                                                 class="w-4 h-4 mr-3 rounded-sm"
                                                 style="margin-right:2px"
                                                 onerror="this.style.display='none'">
                                            <a href="{{ route('domain.ranking', ['domain' => $change->domain]) }}" 
                                               class="text-sm font-medium text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 hover:underline">
                                                {{ $change->domain }}
                                            </a>
                                            <a href="https://{{ $change->domain }}" target="_blank" title="访问 {{ $change->domain }}">
                                                <!-- 纯文本符号 -->
                                                <span class="text-green-500 text-sm" style="margin-left:2px">🌐</span>
                                            </a>
                                            <!-- 新增：访问状态指示器 -->
                                            <span class="domain-test-status ml-2" data-domain="{{ $change->domain }}"></span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                        {{ number_format($change->current_ranking) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @if($change->daily_change !== null)
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                                @if($change->daily_trend === 'up') bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100
                                                @elseif($change->daily_trend === 'down') bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100
                                                @else bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-100
                                                @endif">
                                                @if($change->daily_trend === 'up') ↑
                                                @elseif($change->daily_trend === 'down') ↓
                                                @else →
                                                @endif
                                                {{ number_format(abs($change->daily_change)) }}
                                            </span>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @if($change->week_change !== null)
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                                @if($change->week_trend === 'up') bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100
                                                @elseif($change->week_trend === 'down') bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100
                                                @else bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-100
                                                @endif">
                                                @if($change->week_trend === 'up') ↑
                                                @elseif($change->week_trend === 'down') ↓
                                                @else →
                                                @endif
                                                {{ number_format(abs($change->week_change)) }}
                                            </span>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @if($change->biweek_change !== null)
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                                @if($change->biweek_trend === 'up') bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100
                                                @elseif($change->biweek_trend === 'down') bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100
                                                @else bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-100
                                                @endif">
                                                @if($change->biweek_trend === 'up') ↑
                                                @elseif($change->biweek_trend === 'down') ↓
                                                @else →
                                                @endif
                                                {{ number_format(abs($change->biweek_change)) }}
                                            </span>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @if($change->triweek_change !== null)
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                                @if($change->triweek_trend === 'up') bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100
                                                @elseif($change->triweek_trend === 'down') bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100
                                                @else bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-100
                                                @endif">
                                                @if($change->triweek_trend === 'up') ↑
                                                @elseif($change->triweek_trend === 'down') ↓
                                                @else →
                                                @endif
                                                {{ number_format(abs($change->triweek_change)) }}
                                            </span>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @if($change->month_change !== null)
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                                @if($change->month_trend === 'up') bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100
                                                @elseif($change->month_trend === 'down') bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100
                                                @else bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-100
                                                @endif">
                                                @if($change->month_trend === 'up') ↑
                                                @elseif($change->month_trend === 'down') ↓
                                                @else →
                                                @endif
                                                {{ number_format(abs($change->month_change)) }}
                                            </span>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @if($change->quarter_change !== null)
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                                @if($change->quarter_trend === 'up') bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100
                                                @elseif($change->quarter_trend === 'down') bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100
                                                @else bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-100
                                                @endif">
                                                @if($change->quarter_trend === 'up') ↑
                                                @elseif($change->quarter_trend === 'down') ↓
                                                @else →
                                                @endif
                                                {{ number_format(abs($change->quarter_change)) }}
                                            </span>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @if($change->year_change !== null)
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                                @if($change->year_trend === 'up') bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100
                                                @elseif($change->year_trend === 'down') bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100
                                                @else bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-100
                                                @endif">
                                                @if($change->year_trend === 'up') ↑
                                                @elseif($change->year_trend === 'down') ↓
                                                @else →
                                                @endif
                                                {{ number_format(abs($change->year_change)) }}
                                            </span>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                        暂无数据
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- 分页导航 -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    {{ $rankingChanges->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 页码跳转功能
    const pageJumpInput = document.getElementById('pageJumpInput');
    const pageJumpBtn = document.getElementById('pageJumpBtn');
    
    function jumpToPage() {
        const page = parseInt(pageJumpInput.value);
        const maxPage = parseInt(pageJumpInput.getAttribute('max'));
        
        if (page && page >= 1 && page <= maxPage) {
            const url = new URL(window.location);
            url.searchParams.set('page', page);
            window.location.href = url.toString();
        } else {
            alert('请输入有效的页码（1 - ' + maxPage + '）');
            pageJumpInput.value = {{ $rankingChanges->currentPage() }};
        }
    }
    
    pageJumpBtn.addEventListener('click', jumpToPage);
    
    // 回车键跳转
    pageJumpInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            jumpToPage();
        }
    });
    
    // 排序选择变化时重新加载页面
    document.getElementById('sortSelect').addEventListener('change', function() {
        const [sort, order] = this.value.split('-');
        const url = new URL(window.location);
        url.searchParams.set('sort', sort);
        url.searchParams.set('order', order);
        url.searchParams.delete('page'); // 重置到第一页
        window.location.href = url.toString();
    });

    // 应用过滤器
    document.getElementById('applyFilter').addEventListener('click', function() {
        const filterField = document.getElementById('filterField').value;
        const filterValue = document.getElementById('filterValue').value;
        
        const url = new URL(window.location);
        
        if (filterField && filterValue !== '') {
            url.searchParams.set('filter_field', filterField);
            url.searchParams.set('filter_value', filterValue);
        } else {
            url.searchParams.delete('filter_field');
            url.searchParams.delete('filter_value');
        }
        
        url.searchParams.delete('page'); // 重置到第一页
        window.location.href = url.toString();
    });

    // 清除过滤器
    const clearFilterBtn = document.getElementById('clearFilter');
    if (clearFilterBtn) {
        clearFilterBtn.addEventListener('click', function() {
            const url = new URL(window.location);
            url.searchParams.delete('filter_field');
            url.searchParams.delete('filter_value');
            url.searchParams.delete('page'); // 重置到第一页
            window.location.href = url.toString();
        });
    }

    // 回车键应用过滤器
    document.getElementById('filterValue').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            document.getElementById('applyFilter').click();
        }
    });

    // 过滤字段变化时更新占位符
    document.getElementById('filterField').addEventListener('change', function() {
        const filterValue = document.getElementById('filterValue');
        
        if (this.value === 'current_ranking') {
            filterValue.placeholder = '排名值';
        } else if (this.value) {
            filterValue.placeholder = '变化值';
        } else {
            filterValue.placeholder = '数值';
        }
    });

    // ============ 新增：域名访问测试功能 ============
    let isTestRunning = false;
    let shouldStopTest = false;
    
    const testBtn = document.getElementById('testAllDomains');
    const stopBtn = document.getElementById('stopTest');
    const clearBtn = document.getElementById('clearResults');
    const progressDiv = document.getElementById('testProgress');
    const progressBar = document.getElementById('progressBar');
    const progressText = document.getElementById('progressText');
    const successCount = document.getElementById('successCount');
    const failCount = document.getElementById('failCount');
    const timeoutCount = document.getElementById('timeoutCount');
    
    // 获取所有域名
    function getAllDomains() {
        const domains = [];
        document.querySelectorAll('.domain-test-status').forEach(el => {
            const domain = el.getAttribute('data-domain');
            if (domain) {
                domains.push({
                    domain: domain,
                    element: el
                });
            }
        });
        return domains;
    }
    
    // 测试单个域名
    async function testDomain(domain, timeout = 5000) {
        const protocols = ['https://', 'http://'];
        
        for (const protocol of protocols) {
            try {
                // 使用Image对象测试（通过favicon）
                const result = await new Promise((resolve) => {
                    const img = new Image();
                    const timer = setTimeout(() => {
                        img.src = '';
                        resolve({ success: false, protocol: protocol, method: 'timeout' });
                    }, timeout);
                    
                    img.onload = () => {
                        clearTimeout(timer);
                        resolve({ success: true, protocol: protocol, method: 'favicon' });
                    };
                    
                    img.onerror = () => {
                        clearTimeout(timer);
                        // 尝试使用fetch（会受CORS限制，但某些情况下仍能判断）
                        fetch(protocol + domain, { mode: 'no-cors', method: 'HEAD' })
                            .then(() => {
                                resolve({ success: true, protocol: protocol, method: 'fetch' });
                            })
                            .catch(() => {
                                resolve({ success: false, protocol: protocol, method: 'error' });
                            });
                    };
                    
                    img.src = protocol + domain + '/favicon.ico';
                });
                
                if (result.success) {
                    return result;
                }
            } catch (error) {
                console.error(`Error testing ${domain}:`, error);
            }
        }
        
        return { success: false, protocol: null, method: 'failed' };
    }
    
    // 更新域名状态显示
    function updateDomainStatus(element, status) {
        element.innerHTML = '';
        
        if (status.success) {
            element.innerHTML = `
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100">
                    ✓ ${status.protocol ? status.protocol.replace('://', '') : ''}
                </span>
            `;
        } else if (status.method === 'timeout') {
            element.innerHTML = `
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100">
                    ⏱ 超时
                </span>
            `;
        } else {
            element.innerHTML = `
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100">
                    ✗ 失败
                </span>
            `;
        }
    }
    
    // 批量测试所有域名
    async function testAllDomains() {
        if (isTestRunning) return;
        
        isTestRunning = true;
        shouldStopTest = false;
        
        const domains = getAllDomains();
        const total = domains.length;
        let completed = 0;
        let success = 0;
        let fail = 0;
        let timeout = 0;
        
        // 显示进度条
        testBtn.classList.add('hidden');
        stopBtn.classList.remove('hidden');
        progressDiv.classList.remove('hidden');
        
        // 更新进度
        function updateProgress() {
            const percent = (completed / total * 100).toFixed(1);
            progressBar.style.width = percent + '%';
            progressText.textContent = `${completed}/${total}`;
            successCount.textContent = success;
            failCount.textContent = fail;
            timeoutCount.textContent = timeout;
        }
        
        // 设置测试中状态
        domains.forEach(item => {
            item.element.innerHTML = `
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-100">
                    <svg class="animate-spin h-3 w-3 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    测试中
                </span>
            `;
        });
        
        // 批量测试（并发限制）
        const concurrency = 5; // 同时测试5个域名
        for (let i = 0; i < domains.length; i += concurrency) {
            if (shouldStopTest) break;
            
            const batch = domains.slice(i, i + concurrency);
            const promises = batch.map(async (item) => {
                if (shouldStopTest) return;
                
                const result = await testDomain(item.domain);
                updateDomainStatus(item.element, result);
                
                if (result.success) {
                    success++;
                } else if (result.method === 'timeout') {
                    timeout++;
                } else {
                    fail++;
                }
                
                completed++;
                updateProgress();
            });
            
            await Promise.all(promises);
        }
        
        // 完成测试
        isTestRunning = false;
        stopBtn.classList.add('hidden');
        clearBtn.classList.remove('hidden');
        testBtn.textContent = '重新测试';
        testBtn.classList.remove('hidden');
    }
    
    // 停止测试
    stopBtn.addEventListener('click', function() {
        shouldStopTest = true;
        stopBtn.classList.add('hidden');
        testBtn.classList.remove('hidden');
        clearBtn.classList.remove('hidden');
    });
    
    // 清除结果
    clearBtn.addEventListener('click', function() {
        document.querySelectorAll('.domain-test-status').forEach(el => {
            el.innerHTML = '';
        });
        progressDiv.classList.add('hidden');
        clearBtn.classList.add('hidden');
        testBtn.textContent = '测试所有域名';
    });
    
    // 开始测试
    testBtn.addEventListener('click', testAllDomains);
});
</script>
</x-app-layout>