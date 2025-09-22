<x-app-layout>
    {{-- 页面标题 --}}
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex-1">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    域名分类统计
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">按类别查看所有域名分布</p>
            </div>
            <div class="text-sm text-gray-600 dark:text-gray-400">
                总计 {{ number_format($totalDomains) }} 个域名
            </div>
        </div>
    </x-slot>

    {{-- 页面主要内容 --}}
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            {{-- 统计信息、排序控制和搜索框 --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex flex-wrap justify-between items-center space-y-4 md:space-y-0">
                        {{-- 统计信息 --}}
                        <div class="flex items-center space-x-6">
                            <div class="flex items-center">
                                <div class="p-2 bg-blue-500 bg-opacity-10 rounded-full mr-3">
                                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-gray-600 dark:text-gray-400">分类总数</p>
                                    <p class="text-lg font-bold text-gray-900 dark:text-white">
                                        {{ $categoriesWithTranslation->count() }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center">
                                <div class="p-2 bg-green-500 bg-opacity-10 rounded-full mr-3">
                                    <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-gray-600 dark:text-gray-400">已分类域名</p>
                                    <p class="text-lg font-bold text-gray-900 dark:text-white">
                                        {{ number_format($categoriesWithTranslation->sum('count')) }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center">
                                <div class="p-2 bg-purple-500 bg-opacity-10 rounded-full mr-3">
                                    <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-gray-600 dark:text-gray-400">覆盖率</p>
                                    <p class="text-lg font-bold text-gray-900 dark:text-white">
                                        {{ number_format(($categoriesWithTranslation->sum('count') / $totalDomains) * 100, 1) }}%
                                    </p>
                                </div>
                            </div>
                        </div>

                        {{-- 控制区域 --}}
                        <div class="flex items-center space-x-4">
                            {{-- 搜索框 --}}
                            <div class="flex items-center space-x-2">
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">搜索：</label>
                                <input type="text" 
                                       id="categorySearch" 
                                       placeholder="分类关键词"
                                       class="w-32 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                            </div>

                            {{-- 排序控制 --}}
                            <div class="flex items-center space-x-2">
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">排序：</label>
                                <select id="sortSelect" 
                                        class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                                    <option value="count-desc">域名数量 ↓</option>
                                    <option value="count-asc">域名数量 ↑</option>
                                    <option value="name-asc">分类名称 A-Z</option>
                                    <option value="name-desc">分类名称 Z-A</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 分类统计卡片 --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex flex-wrap justify-between items-center space-y-4 md:space-y-0">
                        <div class="flex items-center">
                            <div class="p-2 bg-blue-500 bg-opacity-10 rounded-full mr-3">
                                <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-600 dark:text-gray-400">域名总数</p>
                                <p class="text-lg font-bold text-gray-900 dark:text-white">
                                    {{ number_format($categoriesWithTranslation->sum('count')) }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center">
                            <div class="p-2 bg-green-500 bg-opacity-10 rounded-full mr-3">
                                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-600 dark:text-gray-400">最大分类</p>
                                <p class="text-lg font-bold text-green-600">
                                    {{ number_format($categoriesWithTranslation->max('count')) }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center">
                            <div class="p-2 bg-purple-500 bg-opacity-10 rounded-full mr-3">
                                <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-600 dark:text-gray-400">平均每分类</p>
                                <p class="text-lg font-bold text-purple-600">
                                    {{ number_format($categoriesWithTranslation->avg('count'), 0) }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center">
                            <div class="p-2 bg-orange-500 bg-opacity-10 rounded-full mr-3">
                                <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-600 dark:text-gray-400">最小分类</p>
                                <p class="text-lg font-bold text-orange-600">
                                    {{ number_format($categoriesWithTranslation->min('count')) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 数据表格 --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        序号
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        英文分类
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        中文分类
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        域名数量
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        占比
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        操作
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="categoryTableBody" class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($categoriesWithTranslation as $index => $categoryData)
                                <tr class="category-row hover:bg-gray-50 dark:hover:bg-gray-700" 
                                    data-count="{{ $categoryData->count }}"
                                    data-name-en="{{ $categoryData->category }}"
                                    data-name-cn="{{ $categoryData->chinese_name }}">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $index + 1 }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <a href="{{ route('domains.category.domains', ['category' => $categoryData->url_category]) }}" 
                                           class="text-sm font-medium text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-200">
                                            {{ $categoryData->category }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <a href="{{ route('domains.category.domains', ['category' => $categoryData->url_category]) }}" 
                                           class="text-sm font-medium text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-200">
                                            {{ $categoryData->chinese_name }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                        @php
                                            $count = $categoryData->count;
                                            if ($count >= 10000) {
                                                $formatted = number_format($count / 1000, 1) . 'K';
                                                $colorClass = 'text-green-600';
                                            } elseif ($count >= 5000) {
                                                $formatted = number_format($count / 1000, 1) . 'K';
                                                $colorClass = 'text-blue-600';
                                            } elseif ($count >= 1000) {
                                                $formatted = number_format($count / 1000, 1) . 'K';
                                                $colorClass = 'text-purple-600';
                                            } else {
                                                $formatted = number_format($count);
                                                $colorClass = 'text-gray-600';
                                            }
                                        @endphp
                                        <span class="{{ $colorClass }} font-semibold">{{ $formatted }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <div class="flex items-center">
                                            <span class="text-sm font-medium text-gray-900 dark:text-gray-100 mr-2">
                                                {{ number_format(($categoryData->count / $totalDomains) * 100, 2) }}%
                                            </span>
                                            <div class="w-24">
                                                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                                    <div class="bg-gradient-to-r from-blue-400 to-blue-600 h-2 rounded-full" 
                                                        style="width: {{ ($categoryData->count / $categoriesWithTranslation->max('count')) * 100 }}%"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <a href="{{ route('domains.category.domains', ['category' => $categoryData->url_category]) }}" 
                                           class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 transition-colors">
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

                    {{-- 没有搜索结果的提示 --}}
                    <div id="noResultsMessage" class="hidden text-center py-8 text-gray-500 dark:text-gray-400">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">未找到匹配的分类</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">请尝试使用不同的关键词搜索</p>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('categorySearch');
            const sortSelect = document.getElementById('sortSelect');
            const tableBody = document.getElementById('categoryTableBody');
            const noResultsMessage = document.getElementById('noResultsMessage');
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

                // 显示或隐藏"无结果"消息
                if (visibleRows === 0 && searchTerm !== '') {
                    noResultsMessage.classList.remove('hidden');
                    tableBody.parentElement.parentElement.style.display = 'none';
                } else {
                    noResultsMessage.classList.add('hidden');
                    tableBody.parentElement.parentElement.style.display = '';
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
                    rankCell.textContent = index + 1;
                });
            }

            // 事件监听器
            searchInput.addEventListener('input', performSearch);
            sortSelect.addEventListener('change', performSort);

            // ESC键清除搜索
            searchInput.addEventListener('keyup', function(e) {
                if (e.key === 'Escape') {
                    this.value = '';
                    rows.forEach(function(row) {
                        row.style.display = '';
                    });
                    noResultsMessage.classList.add('hidden');
                    tableBody.parentElement.parentElement.style.display = '';
                    
                    // 恢复原始排序
                    tableBody.innerHTML = '';
                    originalRows.forEach(row => {
                        row.style.display = '';
                        tableBody.appendChild(row);
                    });
                    updateRowNumbers();
                    sortSelect.value = 'count-desc';
                }
            });
        });
    </script>
</x-app-layout>