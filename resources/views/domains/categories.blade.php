<x-app-layout>
    {{-- 页面标题 --}}
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight mb-2 md:mb-0">
                {{ __('域名分类统计') }}
            </h2>
            <div class="text-sm text-gray-600 dark:text-gray-400">
                <span class="font-bold">总域名数量:</span> {{ number_format($totalDomains) }}
            </div>
        </div>
    </x-slot>

    {{-- 页面主要内容 --}}
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="space-y-8">

                {{-- 统计信息 - 水平一行显示 --}}
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-900/50 p-6 rounded-2xl shadow-sm border dark:border-gray-700">
                    <div class="flex flex-col md:flex-row justify-around items-center text-center space-y-6 md:space-y-0 md:space-x-6">
                        {{-- 分类总数 --}}
                        <div class="flex items-center space-x-4">
                            <div class="flex-shrink-0 w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center text-white shadow-lg">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-blue-600 dark:text-blue-400">分类总数</div>
                                <div class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ $categoriesWithTranslation->count() }}</div>
                            </div>
                        </div>
                        
                        <div class="hidden md:block w-px h-12 bg-gray-200 dark:bg-gray-700"></div>

                        {{-- 已分类域名 --}}
                        <div class="flex items-center space-x-4">
                            <div class="flex-shrink-0 w-12 h-12 bg-green-500 rounded-full flex items-center justify-center text-white shadow-lg">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-green-600 dark:text-green-400">已分类域名</div>
                                <div class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ number_format($categoriesWithTranslation->sum('count')) }}</div>
                            </div>
                        </div>

                        <div class="hidden md:block w-px h-12 bg-gray-200 dark:bg-gray-700"></div>

                        {{-- 平均每分类 --}}
                        <div class="flex items-center space-x-4">
                             <div class="flex-shrink-0 w-12 h-12 bg-purple-500 rounded-full flex items-center justify-center text-white shadow-lg">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6V4m0 16v-2m0-10v2M6 12H4m16 0h-2m-10 0h2m-2-5.657l-1.414-1.414m11.314 0L12 8.414m-8.486 8.486L6.929 15.5M12 15.586l2.828-2.829" />
                                </svg>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-purple-600 dark:text-purple-400">平均每分类</div>
                                <div class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ number_format($categoriesWithTranslation->avg('count'), 0) }}</div>
                            </div>
                        </div>
                    </div>
                </div>


                {{-- 分类列表和搜索 --}}
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-2xl border dark:border-gray-700">
                    <div class="p-6 space-y-6">
                        {{-- 搜索框 --}}
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input type="text" id="categorySearch" placeholder="搜索分类名称..." 
                                class="block w-full rounded-lg border-gray-300 shadow-sm text-base py-3 pl-10
                                    focus:border-indigo-500 focus:ring focus:ring-indigo-200 
                                    focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 
                                    dark:text-gray-200 dark:placeholder-gray-400 transition duration-150 ease-in-out">
                        </div>

                        {{-- 分类列表 --}}
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700/50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-16">排名</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">分类</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">域名数量</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider min-w-[200px]">占比</th>
                                    </tr>
                                </thead>
                                <tbody id="categoryTableBody" class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($categoriesWithTranslation as $index => $categoryData)
                                    <tr class="category-row hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-150">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-500 dark:text-gray-400">
                                            <span class="inline-block w-8 text-center py-1 bg-gray-100 dark:bg-gray-700 rounded-md">{{ $index + 1 }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                            <div class="flex flex-col space-y-2">
                                                <a href="{{ route('domains.category.domains', ['category' => $categoryData->url_category]) }}" 
                                                    class="font-medium text-base text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300 hover:underline">
                                                    {{ $categoryData->chinese_name }}
                                                </a>
                                                <a href="{{ route('domains.category.domains', ['category' => $categoryData->url_category]) }}" 
                                                    class="font-mono text-xs bg-gray-100 dark:bg-gray-900 px-2 py-1 rounded w-fit hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors duration-200">
                                                    {{ $categoryData->category }}
                                                </a>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                                {{ number_format($categoryData->count) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                             <div class="flex items-center space-x-3">
                                                <span class="font-mono w-16 text-right">{{ number_format(($categoryData->count / $totalDomains) * 100, 2) }}%</span>
                                                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                                                    <div class="bg-gradient-to-r from-sky-400 to-blue-500 h-2.5 rounded-full" style="width: {{ ($categoryData->count / $categoriesWithTranslation->max('count')) * 100 }}%"></div>
                                                </div>
                                             </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- 没有找到结果的提示 --}}
                        <div id="noResultsMessage" class="hidden text-center py-16 text-gray-500 dark:text-gray-400">
                            <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 12h6m-6-4h6m2 5.291A7.962 7.962 0 0112 15c-2.34 0-4.291-1.007-5.691-2.413M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-gray-100">未找到匹配的分类</h3>
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">请尝试使用不同的关键词搜索。</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- JavaScript 搜索功能 --}}
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('categorySearch');
            const table = document.querySelector('table');
            const tableBody = document.getElementById('categoryTableBody');
            const noResultsMessage = document.getElementById('noResultsMessage');
            const rows = tableBody.querySelectorAll('.category-row');

            function filterRows() {
                const searchTerm = searchInput.value.toLowerCase().trim();
                let visibleRowsCount = 0;

                rows.forEach((row) => {
                    // 从第二列 (index 1) 的链接中获取所有文本内容
                    const searchableCell = row.cells[1];
                    const cellText = searchableCell.textContent.toLowerCase();
                    
                    const isVisible = cellText.includes(searchTerm);
                    row.style.display = isVisible ? '' : 'none';
                    
                    if (isVisible) {
                        visibleRowsCount++;
                    }
                });

                // 动态更新排名
                let currentRank = 1;
                rows.forEach((row) => {
                    if (row.style.display !== 'none') {
                        const rankCell = row.cells[0].querySelector('span');
                        if(rankCell) {
                           rankCell.textContent = currentRank++;
                        }
                    }
                });


                if (visibleRowsCount === 0) {
                    noResultsMessage.classList.remove('hidden');
                    table.style.display = 'none';
                } else {
                    noResultsMessage.classList.add('hidden');
                    table.style.display = '';
                }
            }

            searchInput.addEventListener('input', filterRows);

            searchInput.addEventListener('keyup', (e) => {
                if (e.key === 'Escape') {
                    searchInput.value = '';
                    filterRows();
                }
            });
        });
    </script>
    @endpush
</x-app-layout>

