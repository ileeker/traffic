<x-app-layout>
    {{-- 页面标题 --}}
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <nav class="flex mb-2" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-3">
                        <li class="inline-flex items-center">
                            <a href="{{ route('domains.categories') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                                <svg class="w-3 h-3 mr-2.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="m19.707 9.293-2-2-7-7a1 1 0 0 0-1.414 0l-7 7-2 2a1 1 0 0 0 1.414 1.414L2 10.414V18a2 2 0 0 0 2 2h3a1 1 0 0 0 1-1v-4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v4a1 1 0 0 0 1 1h3a2 2 0 0 0 2-2v-7.586l.293.293a1 1 0 0 0 1.414-1.414Z"/>
                                </svg>
                                分类统计
                            </a>
                        </li>
                        <li aria-current="page">
                            <div class="flex items-center">
                                <svg class="w-3 h-3 text-gray-400 mx-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
                                </svg>
                                <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2 dark:text-gray-400">{{ $chineseName }}</span>
                            </div>
                        </li>
                    </ol>
                </nav>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    {{ $chineseName }} - 域名列表
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ $originalCategory }}</p>
            </div>
        </div>
    </x-slot>

    {{-- 页面主要内容 --}}
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    {{-- 统计信息 --}}
                    <div class="mb-6 grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                            <div class="text-sm text-gray-600 dark:text-gray-400">域名总数</div>
                            <div class="text-2xl font-bold">{{ number_format($categoryStats->total_count) }}</div>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                            <div class="text-sm text-gray-600 dark:text-gray-400">平均 EMV</div>
                            <div class="text-2xl font-bold">{{ number_format($categoryStats->avg_emv, 0) }}</div>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                            <div class="text-sm text-gray-600 dark:text-gray-400">最高 EMV</div>
                            <div class="text-2xl font-bold">{{ number_format($categoryStats->max_emv, 0) }}</div>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                            <div class="text-sm text-gray-600 dark:text-gray-400">最低 EMV</div>
                            <div class="text-2xl font-bold">{{ number_format($categoryStats->min_emv, 0) }}</div>
                        </div>
                    </div>

                    {{-- 排序和搜索工具栏 --}}
                    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-3 sm:space-y-0">
                        <div class="flex items-center space-x-4">
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">排序:</label>
                            <select id="sortSelect" onchange="changeSorting()" 
                                class="rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300">
                                <option value="current_emv-desc" {{ $sortBy == 'current_emv' && $sortOrder == 'desc' ? 'selected' : '' }}>EMV (高到低)</option>
                                <option value="current_emv-asc" {{ $sortBy == 'current_emv' && $sortOrder == 'asc' ? 'selected' : '' }}>EMV (低到高)</option>
                                <option value="domain-asc" {{ $sortBy == 'domain' && $sortOrder == 'asc' ? 'selected' : '' }}>域名 (A-Z)</option>
                                <option value="domain-desc" {{ $sortBy == 'domain' && $sortOrder == 'desc' ? 'selected' : '' }}>域名 (Z-A)</option>
                                <option value="registered_at-desc" {{ $sortBy == 'registered_at' && $sortOrder == 'desc' ? 'selected' : '' }}>注册时间 (新到老)</option>
                                <option value="registered_at-asc" {{ $sortBy == 'registered_at' && $sortOrder == 'asc' ? 'selected' : '' }}>注册时间 (老到新)</option>
                            </select>
                        </div>
                        
                        <div class="flex items-center space-x-2">
                            <input type="text" id="domainSearch" placeholder="搜索域名..." 
                                class="block w-full rounded-md border-gray-300 shadow-sm 
                                    focus:border-indigo-300 focus:ring focus:ring-indigo-200 
                                    focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 
                                    dark:text-gray-300">
                        </div>
                    </div>

                    {{-- 域名列表 --}}
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        序号
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        域名
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        EMV
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        全球排名
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        注册时间
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="domainTableBody" class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($domains as $index => $domain)
                                <tr class="domain-row hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ ($domains->currentPage() - 1) * $domains->perPage() + $index + 1 }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="{{ route('domain.ranking', ['domain' => $domain->domain]) }}" 
                                            class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 hover:underline">
                                            {{ $domain->domain }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                            {{ $domain->current_emv >= 1000000 ? 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100' : 
                                               ($domain->current_emv >= 100000 ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100' : 
                                               'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-100') }}">
                                            {{ number_format($domain->current_emv) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        @if($domain->global_rank)
                                            #{{ number_format($domain->global_rank) }}
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        @if($domain->websiteIntroduction && $domain->websiteIntroduction->registered_at)
                                            <span title="{{ $domain->websiteIntroduction->registered_at->format('Y-m-d H:i:s') }}">
                                                {{ $domain->websiteIntroduction->registered_at->format('Y-m-d') }}
                                            </span>
                                        @else
                                            <span class="text-gray-400">未知</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                        该分类下暂无域名数据
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- 没有搜索结果的提示 --}}
                    <div id="noResultsMessage" class="hidden text-center py-8 text-gray-500 dark:text-gray-400">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">未找到匹配的域名</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">请尝试使用不同的关键词搜索</p>
                    </div>

                    {{-- 分页导航 --}}
                    @if($domains->hasPages())
                    <div class="mt-6">
                        {{ $domains->links() }}
                    </div>
                    @endif

                </div>
            </div>
        </div>
    </div>

    {{-- JavaScript 功能 --}}
    <script>
        // 排序功能
        function changeSorting() {
            const sortSelect = document.getElementById('sortSelect');
            const [sortBy, sortOrder] = sortSelect.value.split('-');
            
            const url = new URL(window.location);
            url.searchParams.set('sort', sortBy);
            url.searchParams.set('order', sortOrder);
            url.searchParams.delete('page'); // 重置到第一页
            
            window.location.href = url.toString();
        }

        // 搜索功能
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('domainSearch');
            const tableBody = document.getElementById('domainTableBody');
            const noResultsMessage = document.getElementById('noResultsMessage');
            const rows = tableBody.querySelectorAll('.domain-row');

            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase().trim();
                let visibleRows = 0;

                rows.forEach(function(row) {
                    const domainName = row.cells[1].textContent.toLowerCase();
                    
                    if (domainName.includes(searchTerm)) {
                        row.style.display = '';
                        visibleRows++;
                    } else {
                        row.style.display = 'none';
                    }
                });

                // 显示或隐藏"无结果"消息
                if (visibleRows === 0 && searchTerm !== '') {
                    noResultsMessage.classList.remove('hidden');
                    tableBody.parentElement.style.display = 'none';
                } else {
                    noResultsMessage.classList.add('hidden');
                    tableBody.parentElement.style.display = '';
                }
            });

            // 清空搜索
            searchInput.addEventListener('keyup', function(e) {
                if (e.key === 'Escape' || this.value === '') {
                    this.value = '';
                    rows.forEach(function(row) {
                        row.style.display = '';
                    });
                    noResultsMessage.classList.add('hidden');
                    tableBody.parentElement.style.display = '';
                }
            });
        });

        // URL 参数保持（用于分页时保持排序）
        document.addEventListener('DOMContentLoaded', function() {
            // 处理分页链接，保持当前的排序参数
            const paginationLinks = document.querySelectorAll('.pagination a');
            const currentParams = new URLSearchParams(window.location.search);
            
            paginationLinks.forEach(link => {
                if (link.href && !link.href.includes('javascript:')) {
                    const url = new URL(link.href);
                    
                    // 保持排序参数
                    if (currentParams.has('sort')) {
                        url.searchParams.set('sort', currentParams.get('sort'));
                    }
                    if (currentParams.has('order')) {
                        url.searchParams.set('order', currentParams.get('order'));
                    }
                    
                    link.href = url.toString();
                }
            });
        });
    </script>
</x-app-layout>