<x-app-layout>
    {{-- 页面标题 --}}
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('域名分类统计') }}
            </h2>
            <div class="text-sm text-gray-600 dark:text-gray-400">
                总域名数量: {{ number_format($totalDomains) }}
            </div>
        </div>
    </x-slot>

    {{-- 页面主要内容 --}}
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    {{-- 搜索框 --}}
                    <div class="mb-6">
                        <input type="text" id="categorySearch" placeholder="搜索分类..." 
                            class="block w-full rounded-md border-gray-300 shadow-sm 
                                focus:border-indigo-300 focus:ring focus:ring-indigo-200 
                                focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 
                                dark:text-gray-300">
                    </div>

                    {{-- 统计信息 --}}
                    <div class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                            <div class="text-sm text-gray-600 dark:text-gray-400">分类总数</div>
                            <div class="text-2xl font-bold">{{ $categoriesWithTranslation->count() }}</div>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                            <div class="text-sm text-gray-600 dark:text-gray-400">已分类域名</div>
                            <div class="text-2xl font-bold">{{ number_format($categoriesWithTranslation->sum('count')) }}</div>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                            <div class="text-sm text-gray-600 dark:text-gray-400">平均每分类</div>
                            <div class="text-2xl font-bold">{{ number_format($categoriesWithTranslation->avg('count'), 0) }}</div>
                        </div>
                    </div>

                    {{-- 分类列表 --}}
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
                                </tr>
                            </thead>
                            <tbody id="categoryTableBody" class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($categoriesWithTranslation as $index => $categoryData)
                                <tr class="category-row hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ $index + 1 }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        <span class="font-mono text-xs bg-gray-100 dark:bg-gray-600 px-2 py-1 rounded">
                                            {{ $categoryData->category }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        <span class="font-medium">{{ $categoryData->chinese_name }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100">
                                            {{ number_format($categoryData->count) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ number_format(($categoryData->count / $totalDomains) * 100, 2) }}%
                                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5 mt-1">
                                            <div class="bg-blue-600 h-1.5 rounded-full" style="width: {{ ($categoryData->count / $categoriesWithTranslation->max('count')) * 100 }}%"></div>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- 没有找到结果的提示 --}}
                    <div id="noResultsMessage" class="hidden text-center py-8 text-gray-500 dark:text-gray-400">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 12h6m-6-4h6m2 5.291A7.962 7.962 0 0112 15c-2.34 0-4.291-1.007-5.691-2.413M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">未找到匹配的分类</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">请尝试使用不同的关键词搜索</p>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- JavaScript 搜索功能 --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('categorySearch');
            const tableBody = document.getElementById('categoryTableBody');
            const noResultsMessage = document.getElementById('noResultsMessage');
            const rows = tableBody.querySelectorAll('.category-row');

            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase().trim();
                let visibleRows = 0;

                rows.forEach(function(row, index) {
                    const englishCategory = row.cells[1].textContent.toLowerCase();
                    const chineseCategory = row.cells[2].textContent.toLowerCase();
                    
                    if (englishCategory.includes(searchTerm) || chineseCategory.includes(searchTerm)) {
                        row.style.display = '';
                        // 重新编号可见的行
                        row.cells[0].textContent = visibleRows + 1;
                        visibleRows++;
                    } else {
                        row.style.display = 'none';
                    }
                });

                // 显示或隐藏"无结果"消息
                if (visibleRows === 0) {
                    noResultsMessage.classList.remove('hidden');
                    tableBody.parentElement.style.display = 'none';
                } else {
                    noResultsMessage.classList.add('hidden');
                    tableBody.parentElement.style.display = '';
                }
            });

            // 清空搜索时重置编号
            searchInput.addEventListener('keyup', function(e) {
                if (e.key === 'Escape' || this.value === '') {
                    this.value = '';
                    rows.forEach(function(row, index) {
                        row.style.display = '';
                        row.cells[0].textContent = index + 1;
                    });
                    noResultsMessage.classList.add('hidden');
                    tableBody.parentElement.style.display = '';
                }
            });
        });
    </script>
</x-app-layout>