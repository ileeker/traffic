{{-- 统计信息 --}}
                    <div class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="bg-gradient-to-r from-blue-50 to-blue-100 dark:from-blue-900 dark:to-blue-800 p-6 rounded-lg border border-blue-200 dark:border-blue-700">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14-7H5m14 14H5"></path>
                                        </svg>
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
                    <div class="mb-8">
                        <input type="text" id="categorySearch" placeholder="搜索分类..." 
                            class="block w-full rounded-md border-gray-300 shadow-sm text-base py-3 px-4
                                focus:border-indigo-300 focus:ring focus:ring-indigo-200 
                                focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 
                                dark:text-gray-300 dark:placeholder-gray-400">
                    </div>

                    {{-- 统计信息 - 水平一行显示 --}}
                    <div class="mb-8 bg-gradient-to-r from-blue-50 to-purple-50 dark:from-gray-800 dark:to-gray-700 p-8 rounded-lg border border-gray-200 dark:border-gray-600">
                        <div class="flex justify-around items-center text-center">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14-7H5m14 14H5"></path>
                                    </svg>
                                </div>
                                <div>
                                    <div class="text-base font-medium text-blue-600 dark:text-blue-400">分类总数</div>
                                    <div class="text-3xl font-bold text-blue-900 dark:text-blue-100">{{ $categoriesWithTranslation->count() }}</div>
                                </div>
                            </div>
                            
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <div class="text-base font-medium text-green-600 dark:text-green-400">已分类域名</div>
                                    <div class="text-3xl font-bold text-green-900 dark:text-green-100">{{ number_format($categoriesWithTranslation->sum('count')) }}</div>
                                </div>
                            </div>
                            
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 bg-purple-500 rounded-full flex items-center justify-center">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <div class="text-base font-medium text-purple-600 dark:text-purple-400">平均每分类</div>
                                    <div class="text-3xl font-bold text-purple-900 dark:text-purple-100">{{ number_format($categoriesWithTranslation->avg('count'), 0) }}</div>
                                </div>
                            </div>
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
                                    <td class="px-6 py-4 whitespace-nowrap text-base font-medium text-gray-900 dark:text-gray-100">
                                        {{ $index + 1 }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        <a href="{{ route('domains.category.domains', ['category' => $categoryData->url_category]) }}" 
                                            class="font-mono text-sm bg-gray-100 dark:bg-gray-600 px-3 py-2 rounded hover:bg-gray-200 dark:hover:bg-gray-500 transition-colors duration-200">
                                            {{ $categoryData->category }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        <a href="{{ route('domains.category.domains', ['category' => $categoryData->url_category]) }}" 
                                            class="font-medium text-base text-blue-600 hover:text-blue-500 dark:text-blue-400 dark:hover:text-blue-300 hover:underline">
                                            {{ $categoryData->chinese_name }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-base text-gray-900 dark:text-gray-100">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100">
                                            {{ number_format($categoryData->count) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-base text-gray-500 dark:text-gray-400">
                                        {{ number_format(($categoryData->count / $totalDomains) * 100, 2) }}%
                                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 mt-2">
                                            <div class="bg-blue-600 h-2 rounded-full" style="width: {{ ($categoryData->count / $categoriesWithTranslation->max('count')) * 100 }}%"></div>
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