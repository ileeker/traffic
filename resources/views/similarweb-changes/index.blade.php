<x-app-layout>
    @php
    // 格式化数字为 k/m/b
    function formatNumber($num) {
        if ($num >= 1000000000) {
            return number_format($num / 1000000000, 2) . 'B';
        } elseif ($num >= 1000000) {
            return number_format($num / 1000000, 2) . 'M';
        } elseif ($num >= 1000) {
            return number_format($num / 1000, 2) . 'K';
        }
        return number_format($num, 2);
    }
    
    // 格式化增长率
    function formatGrowthRate($rate) {
        if ($rate === null) {
            return '-';
        }
        $sign = $rate >= 0 ? '+' : '';
        return $sign . number_format($rate, 2) . '%';
    }
    @endphp
    
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                EMV 变化数据 - {{ $recordMonth }}
            </h2>
            <div class="flex items-center space-x-4">
                <select id="monthSelector" 
                        class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                    @foreach($availableMonths as $month)
                        <option value="{{ $month }}" {{ $month == $recordMonth ? 'selected' : '' }}>
                            {{ $month }}
                        </option>
                    @endforeach
                </select>
                <div class="text-sm text-gray-600 dark:text-gray-400">
                    本月记录 {{ number_format($monthCount) }} 条
                </div>
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
                                        第 {{ $similarwebChanges->currentPage() }} 页 / 共 {{ $similarwebChanges->lastPage() }} 页
                                    </p>
                                </div>
                            </div>

                            <!-- 显示范围 -->
                            <div class="flex items-center">
                                <div class="p-2 bg-green-500 bg-opacity-10 rounded-full mr-3">
                                    <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-gray-600 dark:text-gray-400">显示范围</p>
                                    <p class="text-lg font-bold text-gray-900 dark:text-white">
                                        {{ $similarwebChanges->firstItem() ?? 0 }} - {{ $similarwebChanges->lastItem() ?? 0 }}
                                    </p>
                                </div>
                            </div>

                            <!-- 过滤后记录数 -->
                            @if($filterField)
                            <div class="flex items-center">
                                <div class="p-2 bg-yellow-500 bg-opacity-10 rounded-full mr-3">
                                    <svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-gray-600 dark:text-gray-400">过滤结果</p>
                                    <p class="text-lg font-bold text-gray-900 dark:text-white">
                                        {{ number_format($filteredCount) }} 条
                                    </p>
                                </div>
                            </div>
                            @endif
                        </div>

                        <!-- 控制面板 -->
                        <div class="flex items-center space-x-4">
                            <!-- 页码跳转 -->
                            <div class="flex items-center space-x-2">
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">跳转：</label>
                                <input type="number" 
                                       id="pageJumpInput"
                                       placeholder="页码"
                                       value="{{ $similarwebChanges->currentPage() }}"
                                       min="1"
                                       max="{{ $similarwebChanges->lastPage() }}"
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
                                    <optgroup label="EMV值">
                                        <option value="current_emv" {{ $filterField == 'current_emv' ? 'selected' : '' }}>EMV ≥</option>
                                    </optgroup>
                                    <optgroup label="变化值">
                                        <option value="month_emv_change" {{ $filterField == 'month_emv_change' ? 'selected' : '' }}>月变化 ≥</option>
                                        <option value="quarter_emv_change" {{ $filterField == 'quarter_emv_change' ? 'selected' : '' }}>季度变化 ≥</option>
                                        <option value="halfyear_emv_change" {{ $filterField == 'halfyear_emv_change' ? 'selected' : '' }}>半年变化 ≥</option>
                                        <option value="year_emv_change" {{ $filterField == 'year_emv_change' ? 'selected' : '' }}>年变化 ≥</option>
                                    </optgroup>
                                    <optgroup label="增长率">
                                        <option value="month_emv_growth_rate" {{ $filterField == 'month_emv_growth_rate' ? 'selected' : '' }}>月增长率 ≥ (%)</option>
                                        <option value="quarter_emv_growth_rate" {{ $filterField == 'quarter_emv_growth_rate' ? 'selected' : '' }}>季度增长率 ≥ (%)</option>
                                        <option value="halfyear_emv_growth_rate" {{ $filterField == 'halfyear_emv_growth_rate' ? 'selected' : '' }}>半年增长率 ≥ (%)</option>
                                        <option value="year_emv_growth_rate" {{ $filterField == 'year_emv_growth_rate' ? 'selected' : '' }}>年增长率 ≥ (%)</option>
                                    </optgroup>
                                </select>
                                <input type="number" 
                                       id="filterValue"
                                       placeholder="数值"
                                       value="{{ $filterValue }}"
                                       step="any"
                                       class="w-24 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
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
                                    <optgroup label="基础排序">
                                        <option value="current_emv-desc" {{ $sortBy == 'current_emv' && $sortOrder == 'desc' ? 'selected' : '' }}>EMV (高→低)</option>
                                        <option value="current_emv-asc" {{ $sortBy == 'current_emv' && $sortOrder == 'asc' ? 'selected' : '' }}>EMV (低→高)</option>
                                        <option value="domain-asc" {{ $sortBy == 'domain' && $sortOrder == 'asc' ? 'selected' : '' }}>域名 (A→Z)</option>
                                        <option value="domain-desc" {{ $sortBy == 'domain' && $sortOrder == 'desc' ? 'selected' : '' }}>域名 (Z→A)</option>
                                    </optgroup>
                                    <optgroup label="变化值排序">
                                        <option value="month_emv_change-desc" {{ $sortBy == 'month_emv_change' && $sortOrder == 'desc' ? 'selected' : '' }}>月增长最多</option>
                                        <option value="month_emv_change-asc" {{ $sortBy == 'month_emv_change' && $sortOrder == 'asc' ? 'selected' : '' }}>月下降最多</option>
                                        <option value="quarter_emv_change-desc" {{ $sortBy == 'quarter_emv_change' && $sortOrder == 'desc' ? 'selected' : '' }}>季度增长最多</option>
                                        <option value="quarter_emv_change-asc" {{ $sortBy == 'quarter_emv_change' && $sortOrder == 'asc' ? 'selected' : '' }}>季度下降最多</option>
                                        <option value="halfyear_emv_change-desc" {{ $sortBy == 'halfyear_emv_change' && $sortOrder == 'desc' ? 'selected' : '' }}>半年增长最多</option>
                                        <option value="halfyear_emv_change-asc" {{ $sortBy == 'halfyear_emv_change' && $sortOrder == 'asc' ? 'selected' : '' }}>半年下降最多</option>
                                        <option value="year_emv_change-desc" {{ $sortBy == 'year_emv_change' && $sortOrder == 'desc' ? 'selected' : '' }}>年增长最多</option>
                                        <option value="year_emv_change-asc" {{ $sortBy == 'year_emv_change' && $sortOrder == 'asc' ? 'selected' : '' }}>年下降最多</option>
                                    </optgroup>
                                    <optgroup label="增长率排序">
                                        <option value="month_emv_growth_rate-desc" {{ $sortBy == 'month_emv_growth_rate' && $sortOrder == 'desc' ? 'selected' : '' }}>月增长率最高</option>
                                        <option value="month_emv_growth_rate-asc" {{ $sortBy == 'month_emv_growth_rate' && $sortOrder == 'asc' ? 'selected' : '' }}>月下降率最高</option>
                                        <option value="quarter_emv_growth_rate-desc" {{ $sortBy == 'quarter_emv_growth_rate' && $sortOrder == 'desc' ? 'selected' : '' }}>季度增长率最高</option>
                                        <option value="quarter_emv_growth_rate-asc" {{ $sortBy == 'quarter_emv_growth_rate' && $sortOrder == 'asc' ? 'selected' : '' }}>季度下降率最高</option>
                                        <option value="halfyear_emv_growth_rate-desc" {{ $sortBy == 'halfyear_emv_growth_rate' && $sortOrder == 'desc' ? 'selected' : '' }}>半年增长率最高</option>
                                        <option value="halfyear_emv_growth_rate-asc" {{ $sortBy == 'halfyear_emv_growth_rate' && $sortOrder == 'asc' ? 'selected' : '' }}>半年下降率最高</option>
                                        <option value="year_emv_growth_rate-desc" {{ $sortBy == 'year_emv_growth_rate' && $sortOrder == 'desc' ? 'selected' : '' }}>年增长率最高</option>
                                        <option value="year_emv_growth_rate-asc" {{ $sortBy == 'year_emv_growth_rate' && $sortOrder == 'asc' ? 'selected' : '' }}>年下降率最高</option>
                                    </optgroup>
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
                                        当前 EMV
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        月变化
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        季度变化
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        半年变化
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        年变化
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($similarwebChanges as $change)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <img src="https://www.google.com/s2/favicons?domain={{ $change->domain }}" 
                                                 alt="{{ $change->domain }}" 
                                                 class="w-4 h-4 mr-2 rounded-sm"
                                                 onerror="this.style.display='none'">
                                            <a href="{{ route('domain.ranking', ['domain' => $change->domain]) }}" 
                                               class="text-sm font-medium text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 hover:underline">
                                                {{ $change->domain }}
                                            </a>
                                            <a href="https://{{ $change->domain }}" target="_blank" title="访问 {{ $change->domain }}" class="ml-1">
                                                <span class="text-green-500 text-sm">🌐</span>
                                            </a>
                                            <!-- 访问状态指示器 -->
                                            <span class="domain-test-status ml-2" data-domain="{{ $change->domain }}"></span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-semibold text-gray-900 dark:text-white">
                                            {{ formatNumber($change->current_emv) }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @if($change->month_emv_change !== null)
                                            <div class="flex flex-col">
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                                    @if($change->month_emv_trend === 'up') bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100
                                                    @elseif($change->month_emv_trend === 'down') bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100
                                                    @else bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-100
                                                    @endif">
                                                    @if($change->month_emv_trend === 'up') 
                                                        ↑
                                                    @elseif($change->month_emv_trend === 'down')
                                                        ↓
                                                    @else
                                                        →
                                                    @endif
                                                    {{ formatNumber(abs($change->month_emv_change)) }}
                                                </span>
                                                @if($change->month_emv_growth_rate !== null)
                                                    <span class="text-xs mt-1 {{ $change->month_emv_growth_rate > 0 ? 'text-green-600' : 'text-red-600' }}">
                                                        {{ formatGrowthRate($change->month_emv_growth_rate) }}
                                                    </span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @if($change->quarter_emv_change !== null)
                                            <div class="flex flex-col">
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                                    @if($change->quarter_emv_trend === 'up') bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100
                                                    @elseif($change->quarter_emv_trend === 'down') bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100
                                                    @else bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-100
                                                    @endif">
                                                    @if($change->quarter_emv_trend === 'up') 
                                                        ↑
                                                    @elseif($change->quarter_emv_trend === 'down')
                                                        ↓
                                                    @else
                                                        →
                                                    @endif
                                                    {{ formatNumber(abs($change->quarter_emv_change)) }}
                                                </span>
                                                @if($change->quarter_emv_growth_rate !== null)
                                                    <span class="text-xs mt-1 {{ $change->quarter_emv_growth_rate > 0 ? 'text-green-600' : 'text-red-600' }}">
                                                        {{ formatGrowthRate($change->quarter_emv_growth_rate) }}
                                                    </span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @if($change->halfyear_emv_change !== null)
                                            <div class="flex flex-col">
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                                    @if($change->halfyear_emv_trend === 'up') bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100
                                                    @elseif($change->halfyear_emv_trend === 'down') bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100
                                                    @else bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-100
                                                    @endif">
                                                    @if($change->halfyear_emv_trend === 'up') 
                                                        ↑
                                                    @elseif($change->halfyear_emv_trend === 'down')
                                                        ↓
                                                    @else
                                                        →
                                                    @endif
                                                    {{ formatNumber(abs($change->halfyear_emv_change)) }}
                                                </span>
                                                @if($change->halfyear_emv_growth_rate !== null)
                                                    <span class="text-xs mt-1 {{ $change->halfyear_emv_growth_rate > 0 ? 'text-green-600' : 'text-red-600' }}">
                                                        {{ formatGrowthRate($change->halfyear_emv_growth_rate) }}
                                                    </span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @if($change->year_emv_change !== null)
                                            <div class="flex flex-col">
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                                    @if($change->year_emv_trend === 'up') bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100
                                                    @elseif($change->year_emv_trend === 'down') bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100
                                                    @else bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-100
                                                    @endif">
                                                    @if($change->year_emv_trend === 'up') 
                                                        ↑
                                                    @elseif($change->year_emv_trend === 'down')
                                                        ↓
                                                    @else
                                                        →
                                                    @endif
                                                    {{ formatNumber(abs($change->year_emv_change)) }}
                                                </span>
                                                @if($change->year_emv_growth_rate !== null)
                                                    <span class="text-xs mt-1 {{ $change->year_emv_growth_rate > 0 ? 'text-green-600' : 'text-red-600' }}">
                                                        {{ formatGrowthRate($change->year_emv_growth_rate) }}
                                                    </span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
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
                    {{ $similarwebChanges->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 月份选择器
    document.getElementById('monthSelector').addEventListener('change', function() {
        const url = new URL(window.location);
        url.searchParams.set('month', this.value);
        url.searchParams.delete('page'); // 重置到第一页
        window.location.href = url.toString();
    });
    
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
            pageJumpInput.value = {{ $similarwebChanges->currentPage() }};
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
        
        if (this.value === 'current_emv') {
            filterValue.placeholder = 'EMV值';
            filterValue.step = '1';
        } else if (this.value.includes('growth_rate')) {
            filterValue.placeholder = '百分比';
            filterValue.step = '0.01';
        } else if (this.value) {
            filterValue.placeholder = '变化值';
            filterValue.step = '1';
        } else {
            filterValue.placeholder = '数值';
            filterValue.step = 'any';
        }
    });

    // ============ 域名访问测试功能 ============
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
                    ✔ ${status.protocol ? status.protocol.replace('://', '') : ''}
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