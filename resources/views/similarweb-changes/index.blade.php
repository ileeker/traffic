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
                                    <option value="current_emv" {{ $filterField == 'current_emv' ? 'selected' : '' }}>EMV ≥</option>
                                    <option value="month_emv_change" {{ $filterField == 'month_emv_change' ? 'selected' : '' }}>月变化 ≥</option>
                                    <option value="quarter_emv_change" {{ $filterField == 'quarter_emv_change' ? 'selected' : '' }}>季度变化 ≥</option>
                                    <option value="halfyear_emv_change" {{ $filterField == 'halfyear_emv_change' ? 'selected' : '' }}>半年变化 ≥</option>
                                    <option value="year_emv_change" {{ $filterField == 'year_emv_change' ? 'selected' : '' }}>年变化 ≥</option>
                                </select>
                                <input type="number" 
                                       id="filterValue"
                                       placeholder="数值"
                                       value="{{ $filterValue }}"
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
                                    <option value="current_emv-desc" {{ $sortBy == 'current_emv' && $sortOrder == 'desc' ? 'selected' : '' }}>EMV (高→低)</option>
                                    <option value="current_emv-asc" {{ $sortBy == 'current_emv' && $sortOrder == 'asc' ? 'selected' : '' }}>EMV (低→高)</option>
                                    <option value="domain-asc" {{ $sortBy == 'domain' && $sortOrder == 'asc' ? 'selected' : '' }}>域名 (A→Z)</option>
                                    <option value="domain-desc" {{ $sortBy == 'domain' && $sortOrder == 'desc' ? 'selected' : '' }}>域名 (Z→A)</option>
                                    <option value="month_emv_change-desc" {{ $sortBy == 'month_emv_change' && $sortOrder == 'desc' ? 'selected' : '' }}>月增长最多</option>
                                    <option value="month_emv_change-asc" {{ $sortBy == 'month_emv_change' && $sortOrder == 'asc' ? 'selected' : '' }}>月下降最多</option>
                                    <option value="quarter_emv_change-desc" {{ $sortBy == 'quarter_emv_change' && $sortOrder == 'desc' ? 'selected' : '' }}>季度增长最多</option>
                                    <option value="quarter_emv_change-asc" {{ $sortBy == 'quarter_emv_change' && $sortOrder == 'asc' ? 'selected' : '' }}>季度下降最多</option>
                                    <option value="halfyear_emv_change-desc" {{ $sortBy == 'halfyear_emv_change' && $sortOrder == 'desc' ? 'selected' : '' }}>半年增长最多</option>
                                    <option value="halfyear_emv_change-asc" {{ $sortBy == 'halfyear_emv_change' && $sortOrder == 'asc' ? 'selected' : '' }}>半年下降最多</option>
                                    <option value="year_emv_change-desc" {{ $sortBy == 'year_emv_change' && $sortOrder == 'desc' ? 'selected' : '' }}>年增长最多</option>
                                    <option value="year_emv_change-asc" {{ $sortBy == 'year_emv_change' && $sortOrder == 'asc' ? 'selected' : '' }}>年下降最多</option>
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
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-semibold text-gray-900 dark:text-white">
                                            {{ formatNumber($change->current_emv) }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @if($change->month_emv_change !== null)
                                            <div class="flex items-center">
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
                                            </div>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @if($change->quarter_emv_change !== null)
                                            <div class="flex items-center">
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
                                            </div>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @if($change->halfyear_emv_change !== null)
                                            <div class="flex items-center">
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
                                            </div>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @if($change->year_emv_change !== null)
                                            <div class="flex items-center">
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
        } else if (this.value) {
            filterValue.placeholder = '变化值';
        } else {
            filterValue.placeholder = '数值';
        }
    });
});
</script>
</x-app-layout>