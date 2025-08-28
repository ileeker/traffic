<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                批量域名查询结果
            </h2>
            <a href="{{ route('domains.ranking') }}" 
               class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors duration-200">
                重新查询
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- 查询统计 -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex flex-wrap justify-center items-center space-y-4 md:space-y-0">
                        <div class="flex items-center space-x-8">
                            <!-- 查询域名总数 -->
                            <div class="flex items-center">
                                <div class="p-2 bg-blue-500 bg-opacity-10 rounded-full mr-3">
                                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-gray-600 dark:text-gray-400">查询域名总数</p>
                                    <p class="text-lg font-bold text-blue-600">{{ count($domains) }}</p>
                                </div>
                            </div>

                            <!-- 找到数据 -->
                            <div class="flex items-center">
                                <div class="p-2 bg-green-500 bg-opacity-10 rounded-full mr-3">
                                    <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-gray-600 dark:text-gray-400">找到数据</p>
                                    <p class="text-lg font-bold text-green-600">{{ count($foundDomains) }}</p>
                                </div>
                            </div>

                            <!-- 未找到数据 -->
                            <div class="flex items-center">
                                <div class="p-2 bg-red-500 bg-opacity-10 rounded-full mr-3">
                                    <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-gray-600 dark:text-gray-400">未找到数据</p>
                                    <p class="text-lg font-bold text-red-600">{{ count($notFoundDomains) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 查询结果 -->
            @if(count($results) > 0)
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            域名数据详情 ({{ count($results) }} 个)
                        </h3>
                        <button id="exportBtn" 
                                class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors duration-200">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            导出 CSV
                        </button>
                    </div>

                    <!-- 数据表格 -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        域名
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        月份
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        月访问量
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        全球排名
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        流量来源
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        更新时间
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($results as $result)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="p-2 bg-blue-500 bg-opacity-10 rounded-full mr-3">
                                                <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9v-9m0-9v9"></path>
                                                </svg>
                                            </div>
                                            <a href="{{ route('domain.ranking', $result['domain']) }}" 
                                               class="text-sm font-medium text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-200">
                                                {{ $result['domain'] }}
                                            </a>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                        {{ $result['current_month'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                        {{ number_format($result['current_emv']) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                        #{{ number_format($result['global_rank']) }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-wrap gap-1">
                                            <div class="flex items-center px-2 py-1 bg-blue-100 dark:bg-blue-900 rounded text-xs">
                                                <span class="text-blue-800 dark:text-blue-200">直接</span>
                                                <span class="ml-1 font-medium text-blue-900 dark:text-blue-100">
                                                    {{ number_format($result['traffic_sources']['direct'] * 100, 1) }}%
                                                </span>
                                            </div>
                                            <div class="flex items-center px-2 py-1 bg-green-100 dark:bg-green-900 rounded text-xs">
                                                <span class="text-green-800 dark:text-green-200">搜索</span>
                                                <span class="ml-1 font-medium text-green-900 dark:text-green-100">
                                                    {{ number_format($result['traffic_sources']['search'] * 100, 1) }}%
                                                </span>
                                            </div>
                                            <div class="flex items-center px-2 py-1 bg-purple-100 dark:bg-purple-900 rounded text-xs">
                                                <span class="text-purple-800 dark:text-purple-200">推荐</span>
                                                <span class="ml-1 font-medium text-purple-900 dark:text-purple-100">
                                                    {{ number_format($result['traffic_sources']['referrals'] * 100, 1) }}%
                                                </span>
                                            </div>
                                            <div class="flex items-center px-2 py-1 bg-pink-100 dark:bg-pink-900 rounded text-xs">
                                                <span class="text-pink-800 dark:text-pink-200">社交</span>
                                                <span class="ml-1 font-medium text-pink-900 dark:text-pink-100">
                                                    {{ number_format($result['traffic_sources']['social'] * 100, 1) }}%
                                                </span>
                                            </div>
                                            <div class="flex items-center px-2 py-1 bg-orange-100 dark:bg-orange-900 rounded text-xs">
                                                <span class="text-orange-800 dark:text-orange-200">付费</span>
                                                <span class="ml-1 font-medium text-orange-900 dark:text-orange-100">
                                                    {{ number_format($result['traffic_sources']['paid'] * 100, 1) }}%
                                                </span>
                                            </div>
                                            <div class="flex items-center px-2 py-1 bg-red-100 dark:bg-red-900 rounded text-xs">
                                                <span class="text-red-800 dark:text-red-200">邮件</span>
                                                <span class="ml-1 font-medium text-red-900 dark:text-red-100">
                                                    {{ number_format($result['traffic_sources']['mail'] * 100, 1) }}%
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ \Carbon\Carbon::parse($result['last_updated'])->format('Y-m-d') }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            <!-- 未找到数据的域名 -->
            @if(count($notFoundDomains) > 0)
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        未找到数据的域名 ({{ count($notFoundDomains) }} 个)
                    </h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
                        @foreach($notFoundDomains as $domain)
                            <div class="px-3 py-2 bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200 rounded text-sm text-center">
                                {{ $domain }}
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // CSV 导出功能
            document.getElementById('exportBtn').addEventListener('click', function() {
                const results = @json($results);
                
                // CSV 头部
                let csvContent = 'Domain,Month,Monthly_Visits,Global_Rank,Direct_Traffic,Search_Traffic,Referral_Traffic,Social_Traffic,Paid_Traffic,Mail_Traffic,Last_Updated\n';
                
                // CSV 数据
                results.forEach(result => {
                    const row = [
                        result.domain,
                        result.current_month,
                        result.current_emv,
                        result.global_rank,
                        (result.traffic_sources.direct * 100).toFixed(1) + '%',
                        (result.traffic_sources.search * 100).toFixed(1) + '%',
                        (result.traffic_sources.referrals * 100).toFixed(1) + '%',
                        (result.traffic_sources.social * 100).toFixed(1) + '%',
                        (result.traffic_sources.paid * 100).toFixed(1) + '%',
                        (result.traffic_sources.mail * 100).toFixed(1) + '%',
                        new Date(result.last_updated).toISOString().split('T')[0]
                    ];
                    csvContent += row.join(',') + '\n';
                });
                
                // 下载文件
                const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                const link = document.createElement('a');
                const url = URL.createObjectURL(blob);
                link.setAttribute('href', url);
                link.setAttribute('download', 'domains_data_' + new Date().toISOString().split('T')[0] + '.csv');
                link.style.visibility = 'hidden';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            });
        });
    </script>
</x-app-layout>