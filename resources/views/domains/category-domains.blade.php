<x-app-layout>
    {{-- 页面标题 --}}
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex-1">
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
            <div class="text-sm text-gray-600 dark:text-gray-400">
                总计 {{ number_format($categoryStats->total_count) }} 个域名
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
                                    <p class="text-xs font-medium text-gray-600 dark:text-gray-400">当前页面</p>
                                    <p class="text-lg font-bold text-gray-900 dark:text-white">
                                        第 {{ $domains->currentPage() }} 页 / 共 {{ $domains->lastPage() }} 页
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
                                    <p class="text-xs font-medium text-gray-600 dark:text-gray-400">显示范围</p>
                                    <p class="text-lg font-bold text-gray-900 dark:text-white">
                                        {{ $domains->firstItem() ?? 0 }} - {{ $domains->lastItem() ?? 0 }}
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
                                    <p class="text-xs font-medium text-gray-600 dark:text-gray-400">平均 EMV</p>
                                    <p class="text-lg font-bold text-gray-900 dark:text-white">
                                        {{ number_format($categoryStats->avg_emv, 0) }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        {{-- 控制区域 --}}
                        <div class="flex items-center space-x-4">
                            {{-- 页码跳转 --}}
                            <div class="flex items-center space-x-2">
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">跳转：</label>
                                <input type="number" 
                                       id="pageJumpInput"
                                       placeholder="页码"
                                       value="{{ $domains->currentPage() }}"
                                       min="1"
                                       max="{{ $domains->lastPage() }}"
                                       class="w-16 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                                <button id="pageJumpBtn" 
                                        class="px-3 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition-colors duration-200 text-sm">
                                    GO
                                </button>
                            </div>

                            {{-- 搜索框 --}}
                            <div class="flex items-center space-x-2">
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">搜索：</label>
                                <input type="text" 
                                       id="domainSearch" 
                                       placeholder="域名关键词"
                                       class="w-32 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                            </div>

                            {{-- 排序控制 --}}
                            <div class="flex items-center space-x-2">
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">排序：</label>
                                <select id="sortSelect" 
                                        class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                                    <option value="current_emv-desc" {{ $sortBy == 'current_emv' && $sortOrder == 'desc' ? 'selected' : '' }}>访问量 ↓</option>
                                    <option value="current_emv-asc" {{ $sortBy == 'current_emv' && $sortOrder == 'asc' ? 'selected' : '' }}>访问量 ↑</option>
                                    <option value="domain-asc" {{ $sortBy == 'domain' && $sortOrder == 'asc' ? 'selected' : '' }}>域名 A-Z</option>
                                    <option value="domain-desc" {{ $sortBy == 'domain' && $sortOrder == 'desc' ? 'selected' : '' }}>域名 Z-A</option>
                                    <option value="registered_at-desc" {{ $sortBy == 'registered_at' && $sortOrder == 'desc' ? 'selected' : '' }}>注册时间 ↓</option>
                                    <option value="registered_at-asc" {{ $sortBy == 'registered_at' && $sortOrder == 'asc' ? 'selected' : '' }}>注册时间 ↑</option>
                                    <option value="global_rank-asc" {{ $sortBy == 'global_rank' && $sortOrder == 'asc' ? 'selected' : '' }}>排名 ↑</option>
                                    <option value="global_rank-desc" {{ $sortBy == 'global_rank' && $sortOrder == 'desc' ? 'selected' : '' }}>排名 ↓</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 分类统计卡片 --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="flex items-center">
                            <div class="p-2 bg-blue-500 bg-opacity-10 rounded-full mr-3">
                                <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-600 dark:text-gray-400">域名总数</p>
                                <p class="text-lg font-bold text-gray-900 dark:text-white">
                                    {{ number_format($categoryStats->total_count) }}
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
                                <p class="text-xs font-medium text-gray-600 dark:text-gray-400">最高 EMV</p>
                                <p class="text-lg font-bold text-green-600">
                                    @php
                                        $maxEmv = $categoryStats->max_emv;
                                        if ($maxEmv >= 1000000000) {
                                            $formatted = number_format($maxEmv / 1000000000, 2) . 'B';
                                        } elseif ($maxEmv >= 1000000) {
                                            $formatted = number_format($maxEmv / 1000000, 2) . 'M';
                                        } elseif ($maxEmv >= 1000) {
                                            $formatted = number_format($maxEmv / 1000, 2) . 'K';
                                        } else {
                                            $formatted = number_format($maxEmv);
                                        }
                                    @endphp
                                    {{ $formatted }}
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
                                <p class="text-xs font-medium text-gray-600 dark:text-gray-400">平均 EMV</p>
                                <p class="text-lg font-bold text-purple-600">
                                    @php
                                        $avgEmv = $categoryStats->avg_emv;
                                        if ($avgEmv >= 1000000000) {
                                            $formatted = number_format($avgEmv / 1000000000, 2) . 'B';
                                        } elseif ($avgEmv >= 1000000) {
                                            $formatted = number_format($avgEmv / 1000000, 2) . 'M';
                                        } elseif ($avgEmv >= 1000) {
                                            $formatted = number_format($avgEmv / 1000, 2) . 'K';
                                        } else {
                                            $formatted = number_format($avgEmv);
                                        }
                                    @endphp
                                    {{ $formatted }}
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
                                <p class="text-xs font-medium text-gray-600 dark:text-gray-400">最低 EMV</p>
                                <p class="text-lg font-bold text-orange-600">
                                    @php
                                        $minEmv = $categoryStats->min_emv;
                                        if ($minEmv >= 1000000000) {
                                            $formatted = number_format($minEmv / 1000000000, 2) . 'B';
                                        } elseif ($minEmv >= 1000000) {
                                            $formatted = number_format($minEmv / 1000000, 2) . 'M';
                                        } elseif ($minEmv >= 1000) {
                                            $formatted = number_format($minEmv / 1000, 2) . 'K';
                                        } else {
                                            $formatted = number_format($minEmv);
                                        }
                                    @endphp
                                    {{ $formatted }}
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
                                        域名
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        月访问量
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        全球排名
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        注册时间
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        域名年龄
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="domainTableBody" class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($domains as $index => $domain)
                                <tr class="domain-row hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ ($domains->currentPage() - 1) * $domains->perPage() + $index + 1 }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <img src="https://www.google.com/s2/favicons?domain={{ $domain->domain }}" 
                                                 alt="{{ $domain->domain }}" 
                                                 class="w-4 h-4 mr-3 rounded-sm"
                                                 style="margin-right:2px"
                                                 onerror="this.style.display='none'">
                                            <a href="{{ route('domain.ranking', $domain->domain) }}" 
                                               class="text-sm font-medium text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-200">
                                                {{ $domain->domain }}
                                            </a>
                                            <a href="https://{{ $domain->domain }}" target="_blank" title="访问 {{ $domain->domain }}">
                                                <span class="text-green-500 text-sm" style="margin-left:2px">🌐</span>
                                            </a>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                        @php
                                            $emv = $domain->current_emv;
                                            if ($emv >= 1000000000) {
                                                $formatted = number_format($emv / 1000000000, 2) . 'B';
                                                $colorClass = 'text-green-600';
                                            } elseif ($emv >= 1000000) {
                                                $formatted = number_format($emv / 1000000, 2) . 'M';
                                                $colorClass = 'text-blue-600';
                                            } elseif ($emv >= 1000) {
                                                $formatted = number_format($emv / 1000, 2) . 'K';
                                                $colorClass = 'text-purple-600';
                                            } else {
                                                $formatted = number_format($emv);
                                                $colorClass = 'text-gray-600';
                                            }
                                        @endphp
                                        <span class="{{ $colorClass }} font-semibold">{{ $formatted }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @if($domain->global_rank)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-800 dark:text-indigo-100">
                                                #{{ number_format($domain->global_rank) }}
                                            </span>
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
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        @if($domain->websiteIntroduction && $domain->websiteIntroduction->registered_at)
                                            @php
                                                $years = $domain->websiteIntroduction->registered_at->diffInYears(now());
                                                $months = $domain->websiteIntroduction->registered_at->diffInMonths(now()) % 12;
                                            @endphp
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                {{ $years >= 10 ? 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100' : 
                                                   ($years >= 5 ? 'bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100' : 
                                                   'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-100') }}">
                                                {{ $years }}年{{ $months }}月
                                            </span>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
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
                </div>
            </div>

            {{-- 分页导航 --}}
            @if($domains->hasPages())
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    {{ $domains->appends(request()->query())->links() }}
                </div>
            </div>
            @endif

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
                    pageJumpInput.value = {{ $domains->currentPage() }};
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

            // 搜索功能
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
                    tableBody.parentElement.parentElement.style.display = 'none';
                } else {
                    noResultsMessage.classList.add('hidden');
                    tableBody.parentElement.parentElement.style.display = '';
                }
            });

            // 清空搜索
            searchInput.addEventListener('keyup', function(e) {
                if (e.key === 'Escape') {
                    this.value = '';
                    rows.forEach(function(row) {
                        row.style.display = '';
                    });
                    noResultsMessage.classList.add('hidden');
                    tableBody.parentElement.parentElement.style.display = '';
                }
            });

            // URL 参数保持（用于分页时保持排序）
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