<x-app-layout>
    {{-- 页面标题 --}}
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-2xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('域名分类统计') }}
            </h2>
            <div class="flex items-center space-x-4">
                <div class="text-base text-gray-600 dark:text-gray-400">
                    <span class="text-sm">总域名数量</span>
                    <span class="font-bold text-lg ml-2">{{ number_format($totalDomains) }}</span>
                </div>
            </div>
        </div>
    </x-slot>

    {{-- 页面主要内容 --}}
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            {{-- 统计卡片 --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                {{-- 分类总数卡片 --}}
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-100 text-sm font-medium">分类总数</p>
                            <p class="text-3xl font-bold mt-2">{{ $categoriesWithTranslation->count() }}</p>
                            <p class="text-blue-100 text-xs mt-2">覆盖所有域名类型</p>
                        </div>
                        <div class="bg-white bg-opacity-20 rounded-lg p-3">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14-7H5m14 14H5"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                {{-- 已分类域名卡片 --}}
                <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-green-100 text-sm font-medium">已分类域名</p>
                            <p class="text-3xl font-bold mt-2">{{ number_format($categoriesWithTranslation->sum('count')) }}</p>
                            <p class="text-green-100 text-xs mt-2">{{ number_format(($categoriesWithTranslation->sum('count') / $totalDomains) * 100, 1) }}% 覆盖率</p>
                        </div>
                        <div class="bg-white bg-opacity-20 rounded-lg p-3">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                {{-- 平均每分类卡片 --}}
                <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-purple-100 text-sm font-medium">平均每分类</p>
                            <p class="text-3xl font-bold mt-2">{{ number_format($categoriesWithTranslation->avg('count'), 0) }}</p>
                            <p class="text-purple-100 text-xs mt-2">域名数量</p>
                        </div>
                        <div class="bg-white bg-opacity-20 rounded-lg p-3">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 搜索和筛选控制 --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0">
                        {{-- 搜索框 --}}
                        <div class="w-full sm:w-1/2">
                            <div class="relative">
                                <input type="text" 
                                    id="categorySearch" 
                                    placeholder="搜索分类名称..." 
                                    class="w-full pl-10 pr-4 py-2 text-base rounded-lg border-gray-300 shadow-sm 
                                        focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 
                                        dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 dark:placeholder-gray-400">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        {{-- 排序选项 --}}
                        <div class="flex items-center space-x-4">
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">排序：</label>
                            <select id="sortSelect" 
                                class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                                <option value="count-desc">域名数量 ↓</option>
                                <option value="count-asc">域名数量 ↑</option>
                                <option value="name-asc">分类名称 A-Z</option>
                                <option value="name-desc">分类名称 Z-A</option>
                            </select>
                        </div>
                    </div>

                    {{-- 筛选统计 --}}
                    <div id="filterStats" class="mt-4 hidden">
                        <div class="flex items-center justify-between p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                                </svg>
                                <span class="text-sm text-gray-700 dark:text-gray-300">
                                    找到 <span id="filteredCount" class="font-bold">0</span> 个匹配的分类
                                </span>
                            </div>
                            <button id="clearSearch" 
                                class="px-3 py-1 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors text-sm">
                                清除搜索
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 分类列表表格 --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        排名
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        英文分类
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        中文分类
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        域名数量
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        占比
                                    </th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        操作
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="categoryTableBody" class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($categoriesWithTranslation as $index => $categoryData)
                                <tr class="category-row hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors" 
                                    data-count="{{ $categoryData->count }}"
                                    data-name-en="{{ $categoryData->category }}"
                                    data-name-cn="{{ $categoryData->chinese_name }}">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            @if($index < 3)
                                                @if($index == 0)
                                                    <span class="w-8 h-8 rounded-full bg-yellow-100 text-yellow-800 flex items-center justify-center font-bold text-sm">
                                                        🥇
                                                    </span>
                                                @elseif($index == 1)
                                                    <span class="w-8 h-8 rounded-full bg-gray-100 text-gray-800 flex items-center justify-center font-bold text-sm">
                                                        🥈
                                                    </span>
                                                @else
                                                    <span class="w-8 h-8 rounded-full bg-orange-100 text-orange-800 flex items-center justify-center font-bold text-sm">
                                                        🥉
                                                    </span>
                                                @endif
                                            @else
                                                <span class="w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 flex items-center justify-center font-medium text-sm">
                                                    {{ $index + 1 }}
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <a href="{{ route('domains.category.domains', ['category' => $categoryData->url_category]) }}" 
                                            class="text-base font-medium text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-200 transition-colors">
                                            {{ $categoryData->category }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <a href="{{ route('domains.category.domains', ['category' => $categoryData->url_category]) }}" 
                                            class="text-base font-medium text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-200 transition-colors">
                                            {{ $categoryData->chinese_name }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium 
                                                @if($categoryData->count > 10000) bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100
                                                @elseif($categoryData->count > 5000) bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100
                                                @else bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-100
                                                @endif">
                                                {{ number_format($categoryData->count) }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-1">
                                                <div class="flex items-center">
                                                    <span class="text-sm font-medium text-gray-900 dark:text-gray-100 mr-3">
                                                        {{ number_format(($categoryData->count / $totalDomains) * 100, 2) }}%
                                                    </span>
                                                    <div class="flex-1 max-w-xs">
                                                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                                            <div class="bg-gradient-to-r from-blue-400 to-blue-600 h-2 rounded-full transition-all duration-300" 
                                                                style="width: {{ ($categoryData->count / $categoriesWithTranslation->max('count')) * 100 }}%"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <a href="{{ route('domains.category.domains', ['category' => $categoryData->url_category]) }}" 
                                            class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                                            查看域名
                                            <svg class="ml-1 -mr-0.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                            </svg>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- 没有找到结果的提示 --}}
                    <div id="noResultsMessage" class="hidden">
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 12h6m-6-4h6m2 5.291A7.962 7.962 0 0112 15c-2.34 0-4.291-1.007-5.691-2.413M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">未找到匹配的分类</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">请尝试使用不同的关键词搜索</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Top 分类图表 --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Top 10 分类分布</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <canvas id="categoryPieChart"></canvas>
                        </div>
                        <div>
                            <canvas id="categoryBarChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- JavaScript 功能增强 --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('categorySearch');
            const sortSelect = document.getElementById('sortSelect');
            const tableBody = document.getElementById('categoryTableBody');
            const noResultsMessage = document.getElementById('noResultsMessage');
            const filterStats = document.getElementById('filterStats');
            const filteredCountSpan = document.getElementById('filteredCount');
            const clearSearchBtn = document.getElementById('clearSearch');
            const rows = Array.from(tableBody.querySelectorAll('.category-row'));

            // 原始行顺序
            const originalRows = [...rows];

            // 搜索功能
            function performSearch() {
                const searchTerm = searchInput.value.toLowerCase().trim();
                let visibleRows = 0;

                rows.forEach(function(row) {
                    const englishCategory = row.dataset.nameEn.toLowerCase();
                    const chineseCategory = row.dataset.nameCn.toLowerCase();
                    
                    if (searchTerm === '' || englishCategory.includes(searchTerm) || chineseCategory.includes(searchTerm)) {
                        row.style.display = '';
                        visibleRows++;
                    } else {
                        row.style.display = 'none';
                    }
                });

                // 更新筛选统计
                if (searchTerm !== '') {
                    filterStats.classList.remove('hidden');
                    filteredCountSpan.textContent = visibleRows;
                } else {
                    filterStats.classList.add('hidden');
                }

                // 显示或隐藏"无结果"消息
                if (visibleRows === 0 && searchTerm !== '') {
                    noResultsMessage.classList.remove('hidden');
                    tableBody.style.display = 'none';
                } else {
                    noResultsMessage.classList.add('hidden');
                    tableBody.style.display = '';
                }

                // 重新编号可见的行
                updateRowNumbers();
            }

            // 排序功能
            function performSort() {
                const [sortField, sortOrder] = sortSelect.value.split('-');
                let sortedRows = [...rows];

                sortedRows.sort((a, b) => {
                    let aValue, bValue;

                    if (sortField === 'count') {
                        aValue = parseInt(a.dataset.count);
                        bValue = parseInt(b.dataset.count);
                    } else if (sortField === 'name') {
                        aValue = a.dataset.nameEn.toLowerCase();
                        bValue = b.dataset.nameEn.toLowerCase();
                    }

                    if (sortOrder === 'asc') {
                        return aValue > bValue ? 1 : -1;
                    } else {
                        return aValue < bValue ? 1 : -1;
                    }
                });

                // 重新排列DOM
                tableBody.innerHTML = '';
                sortedRows.forEach(row => {
                    tableBody.appendChild(row);
                });

                updateRowNumbers();
            }

            // 更新行号
            function updateRowNumbers() {
                const visibleRows = rows.filter(row => row.style.display !== 'none');
                visibleRows.forEach((row, index) => {
                    const rankCell = row.cells[0];
                    const rankNumber = index + 1;
                    
                    // 更新排名显示
                    let rankHTML = '';
                    if (rankNumber === 1) {
                        rankHTML = '<span class="w-8 h-8 rounded-full bg-yellow-100 text-yellow-800 flex items-center justify-center font-bold text-sm">🥇</span>';
                    } else if (rankNumber === 2) {
                        rankHTML = '<span class="w-8 h-8 rounded-full bg-gray-100 text-gray-800 flex items-center justify-center font-bold text-sm">🥈</span>';
                    } else if (rankNumber === 3) {
                        rankHTML = '<span class="w-8 h-8 rounded-full bg-orange-100 text-orange-800 flex items-center justify-center font-bold text-sm">🥉</span>';
                    } else {
                        rankHTML = `<span class="w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 flex items-center justify-center font-medium text-sm">${rankNumber}</span>`;
                    }
                    
                    rankCell.innerHTML = `<div class="flex items-center">${rankHTML}</div>`;
                });
            }

            // 清除搜索
            function clearSearch() {
                searchInput.value = '';
                performSearch();
                // 恢复原始排序
                tableBody.innerHTML = '';
                originalRows.forEach(row => {
                    row.style.display = '';
                    tableBody.appendChild(row);
                });
                updateRowNumbers();
                sortSelect.value = 'count-desc';
            }

            // 事件监听器
            searchInput.addEventListener('input', performSearch);
            sortSelect.addEventListener('change', performSort);
            clearSearchBtn.addEventListener('click', clearSearch);

            // ESC键清除搜索
            searchInput.addEventListener('keyup', function(e) {
                if (e.key === 'Escape') {
                    clearSearch();
                }
            });

            // 初始化图表
            const topCategories = @json($categoriesWithTranslation->take(10));
            
            // 饼图
            const pieCtx = document.getElementById('categoryPieChart').getContext('2d');
            new Chart(pieCtx, {
                type: 'doughnut',
                data: {
                    labels: topCategories.map(c => c.chinese_name),
                    datasets: [{
                        data: topCategories.map(c => c.count),
                        backgroundColor: [
                            '#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6',
                            '#EC4899', '#14B8A6', '#F97316', '#6366F1', '#84CC16'
                        ],
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 15,
                                font: {
                                    size: 12
                                }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = ((context.parsed / total) * 100).toFixed(1);
                                    return context.label + ': ' + context.parsed.toLocaleString() + ' (' + percentage + '%)';
                                }
                            }
                        }
                    }
                }
            });

            // 条形图
            const barCtx = document.getElementById('categoryBarChart').getContext('2d');
            new Chart(barCtx, {
                type: 'bar',
                data: {
                    labels: topCategories.map(c => c.chinese_name),
                    datasets: [{
                        label: '域名数量',
                        data: topCategories.map(c => c.count),
                        backgroundColor: '#3B82F6',
                        borderColor: '#2563EB',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return value.toLocaleString();
                                }
                            }
                        },
                        x: {
                            ticks: {
                                maxRotation: 45,
                                minRotation: 45
                            }
                        }
                    }
                }
            });
        });
    </script>
</x-app-layout>