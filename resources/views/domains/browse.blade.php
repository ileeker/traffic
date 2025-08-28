<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                域名数据浏览 - {{ $lastMonth }}
            </h2>
            <div class="text-sm text-gray-600 dark:text-gray-400">
                总计 {{ number_format($totalCount) }} 个域名
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- 统计信息和排序控制 -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex flex-wrap justify-between items-center space-y-4 md:space-y-0">
                        <!-- 统计信息 -->
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
                        </div>

                        <!-- 排序控制 -->
                        <div class="flex items-center space-x-3">
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">排序：</label>
                            <select id="sortSelect" 
                                    class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                                <option value="current_emv-desc" {{ $sortBy == 'current_emv' && $sortOrder == 'desc' ? 'selected' : '' }}>访问量 ↓</option>
                                <option value="current_emv-asc" {{ $sortBy == 'current_emv' && $sortOrder == 'asc' ? 'selected' : '' }}>访问量 ↑</option>
                                <option value="category-asc" {{ $sortBy == 'category' && $sortOrder == 'asc' ? 'selected' : '' }}>分类 A-Z</option>
                                <option value="category-desc" {{ $sortBy == 'category' && $sortOrder == 'desc' ? 'selected' : '' }}>分类 Z-A</option>
                                <option value="ts_direct-desc" {{ $sortBy == 'ts_direct' && $sortOrder == 'desc' ? 'selected' : '' }}>直接流量 ↓</option>
                                <option value="ts_search-desc" {{ $sortBy == 'ts_search' && $sortOrder == 'desc' ? 'selected' : '' }}>搜索流量 ↓</option>
                                <option value="ts_referrals-desc" {{ $sortBy == 'ts_referrals' && $sortOrder == 'desc' ? 'selected' : '' }}>推荐流量 ↓</option>
                                <option value="ts_social-desc" {{ $sortBy == 'ts_social' && $sortOrder == 'desc' ? 'selected' : '' }}>社交流量 ↓</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 热门分类 -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">热门分类 (前10名)</h3>
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
                        @foreach($categoryStats as $stat)
                            <div class="px-3 py-2 bg-gray-100 dark:bg-gray-700 rounded text-center">
                                <div class="text-xs text-gray-600 dark:text-gray-400 truncate">{{ $stat->category }}</div>
                                <div class="font-bold text-sm text-gray-900 dark:text-white">{{ number_format($stat->count) }}</div>
                            </div>
                        @endforeach
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
                                        月访问量
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        分类
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        直接
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        搜索
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        推荐
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        社交
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        付费
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        邮件
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($domains as $domain)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <img src="https://www.google.com/s2/favicons?domain={{ $domain->domain }}" 
                                                 alt="{{ $domain->domain }}" 
                                                 class="w-4 h-4 mr-3 rounded-sm"
                                                 onerror="this.style.display='none'">
                                            <a href="{{ route('domain.ranking', $domain->domain) }}" 
                                               class="text-sm font-medium text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-200">
                                                {{ $domain->domain }}
                                            </a>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                        {{ number_format($domain->current_emv) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-600 dark:text-gray-400">
                                        <div class="max-w-32 truncate">{{ $domain->category }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-blue-600">
                                        {{ number_format($domain->ts_direct * 100, 1) }}%
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-600">
                                        {{ number_format($domain->ts_search * 100, 1) }}%
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-purple-600">
                                        {{ number_format($domain->ts_referrals * 100, 1) }}%
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-pink-600">
                                        {{ number_format($domain->ts_social * 100, 1) }}%
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-orange-600">
                                        {{ number_format($domain->ts_paid_referrals * 100, 1) }}%
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-red-600">
                                        {{ number_format($domain->ts_mail * 100, 1) }}%
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
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
                    {{ $domains->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 排序选择变化时重新加载页面
            document.getElementById('sortSelect').addEventListener('change', function() {
                const [sort, order] = this.value.split('-');
                const url = new URL(window.location);
                url.searchParams.set('sort', sort);
                url.searchParams.set('order', order);
                url.searchParams.delete('page'); // 重置到第一页
                window.location.href = url.toString();
            });
        });
    </script>
</x-app-layout>