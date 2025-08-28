<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-semibold text-2xl text-gray-800 dark:text-gray-200 leading-tight">
                    批量域名查询结果
                </h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    以下是您本次提交的 {{ count($domains) }} 个域名的详细分析报告。
                </p>
            </div>
            <a href="{{ route('domains.ranking') }}" 
               class="flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 text-white dark:text-gray-800 rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-150 ease-in-out">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2M3 12l6.414 6.414a2 2 0 002.828 0L21 12M3 12l6.414-6.414a2 2 0 012.828 0L21 12" />
                </svg>
                重新查询
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                {{-- 查询总数 --}}
                <div class="bg-white dark:bg-gray-800 p-6 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm flex items-center gap-6">
                    <div class="flex-shrink-0 w-12 h-12 bg-blue-100 dark:bg-blue-900/50 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M12 21a9 9 0 110-18 9 9 0 010 18z" /></svg>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">查询域名总数</div>
                        <div class="text-3xl font-bold text-gray-900 dark:text-white">{{ count($domains) }}</div>
                    </div>
                </div>
                {{-- 找到数据 --}}
                <div class="bg-white dark:bg-gray-800 p-6 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm flex items-center gap-6">
                    <div class="flex-shrink-0 w-12 h-12 bg-green-100 dark:bg-green-900/50 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">成功找到</div>
                        <div class="text-3xl font-bold text-gray-900 dark:text-white">{{ count($foundDomains) }}</div>
                    </div>
                </div>
                {{-- 未找到数据 --}}
                <div class="bg-white dark:bg-gray-800 p-6 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm flex items-center gap-6">
                    <div class="flex-shrink-0 w-12 h-12 bg-red-100 dark:bg-red-900/50 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-red-600 dark:text-red-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">未能找到</div>
                        <div class="text-3xl font-bold text-gray-900 dark:text-white">{{ count($notFoundDomains) }}</div>
                    </div>
                </div>
            </div>

            @if(count($results) > 0)
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-xl border border-gray-200 dark:border-gray-700">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            域名数据详情 ({{ count($results) }} 个)
                        </h3>
                        <button id="exportBtn" 
                                class="flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 dark:focus:ring-offset-gray-800 transition-all duration-150 ease-in-out text-sm font-semibold">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            导出 CSV
                        </button>
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">域名</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">月份</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">月访问量</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">全球排名</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">流量来源</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">更新时间</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($results as $result)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/50 transition-colors duration-150">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <a href="{{ route('domain.ranking', $result['domain']) }}" class="flex items-center group">
                                        <img src="https://www.google.com/s2/favicons?domain={{ $result['domain'] }}&sz=32" alt="favicon" class="w-6 h-6 mr-3 rounded-full object-contain bg-gray-100 dark:bg-gray-700 p-0.5">
                                        <div class="text-sm font-medium text-blue-600 dark:text-blue-400 group-hover:text-blue-800 dark:group-hover:text-blue-300 group-hover:underline">
                                            {{ $result['domain'] }}
                                        </div>
                                    </a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                    {{ $result['current_month'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900 dark:text-white">
                                    {{ number_format($result['current_emv']) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                    <span class="font-semibold">#</span>{{ number_format($result['global_rank']) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-wrap gap-1.5">
                                        <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300">D: {{ number_format($result['traffic_sources']['direct'] * 100, 1) }}%</span>
                                        <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300">S: {{ number_format($result['traffic_sources']['search'] * 100, 1) }}%</span>
                                        <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-purple-100 text-purple-800 dark:bg-purple-900/50 dark:text-purple-300">R: {{ number_format($result['traffic_sources']['referrals'] * 100, 1) }}%</span>
                                        <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-pink-100 text-pink-800 dark:bg-pink-900/50 dark:text-pink-300">So: {{ number_format($result['traffic_sources']['social'] * 100, 1) }}%</span>
                                        <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-orange-100 text-orange-800 dark:bg-orange-900/50 dark:text-orange-300">P: {{ number_format($result['traffic_sources']['paid'] * 100, 1) }}%</span>
                                        <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300">M: {{ number_format($result['traffic_sources']['mail'] * 100, 1) }}%</span>
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
            @endif

            @if(count($notFoundDomains) > 0)
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-xl border border-gray-200 dark:border-gray-700">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        未找到数据的域名 ({{ count($notFoundDomains) }} 个)
                    </h3>
                    <div class="flex flex-wrap gap-3">
                        @foreach($notFoundDomains as $domain)
                            <div class="px-3 py-1.5 bg-red-100 dark:bg-red-900/50 text-red-800 dark:text-red-300 rounded-md text-sm font-mono">
                                {{ $domain }}
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const exportBtn = document.getElementById('exportBtn');
            if (!exportBtn) return;

            exportBtn.addEventListener('click', function() {
               
                const results = {!! json_encode($results) !!};
                
                // CSV 头部
                let csvContent = 'Domain,Month,Monthly_Visits,Global_Rank,Direct_Traffic(%),Search_Traffic(%),Referral_Traffic(%),Social_Traffic(%),Paid_Traffic(%),Mail_Traffic(%),Last_Updated\n';
                
                // CSV 数据行
                results.forEach(result => {
                    const row = [
                        result.domain,
                        result.current_month,
                        result.current_emv,
                        result.global_rank,
                        (result.traffic_sources.direct * 100).toFixed(1),
                        (result.traffic_sources.search * 100).toFixed(1),
                        (result.traffic_sources.referrals * 100).toFixed(1),
                        (result.traffic_sources.social * 100).toFixed(1),
                        (result.traffic_sources.paid * 100).toFixed(1),
                        (result.traffic_sources.mail * 100).toFixed(1),
                        new Date(result.last_updated).toISOString().split('T')[0]
                    ];
                    // 处理可能包含逗号的字段
                    const csvRow = row.map(field => `"${String(field).replace(/"/g, '""')}"`).join(',');
                    csvContent += csvRow + '\n';
                });
                
                // 创建并下载文件
                const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                const link = document.createElement('a');
                if (link.download !== undefined) {
                    const url = URL.createObjectURL(blob);
                    const timestamp = new Date().toISOString().split('T')[0];
                    link.setAttribute('href', url);
                    link.setAttribute('download', `domains_data_${timestamp}.csv`);
                    link.style.visibility = 'hidden';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    URL.revokeObjectURL(url);
                }
            });
        });
    </script>
    @endpush
</x-app-layout>