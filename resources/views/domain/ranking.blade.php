<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                域名排名趋势 - {{ $domainRecord->domain }}
            </h2>
            <div class="text-sm text-gray-600 dark:text-gray-400">
                最后更新: {{ $domainRecord->last_updated->format('Y-m-d H:i:s') }}
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- 统计卡片 -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="p-2 bg-blue-500 bg-opacity-10 rounded-full">
                                <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">当前排名</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                    #{{ number_format($domainRecord->current_ranking) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                @php
                    $rankingData = $domainRecord->ranking_data['data'] ?? [];
                    $firstRank = count($rankingData) > 0 ? $rankingData[0]['rank'] : $domainRecord->current_ranking;
                    $lastRank = $domainRecord->current_ranking;
                    $change = $firstRank - $lastRank;
                    $changePercent = $firstRank > 0 ? (($change / $firstRank) * 100) : 0;
                @endphp

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="p-2 {{ $change > 0 ? 'bg-green-500' : 'bg-red-500' }} bg-opacity-10 rounded-full">
                                <svg class="w-6 h-6 {{ $change > 0 ? 'text-green-500' : 'text-red-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    @if($change > 0)
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
                                    @else
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                                    @endif
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">排名变化</p>
                                <p class="text-2xl font-bold {{ $change > 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $change > 0 ? '+' : '' }}{{ number_format($change) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="p-2 bg-purple-500 bg-opacity-10 rounded-full">
                                <svg class="w-6 h-6 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">记录时间</p>
                                <p class="text-lg font-bold text-gray-900 dark:text-white">
                                    {{ count($rankingData) }} 天
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 图表区域 -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">排名趋势图</h3>
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            {{ $domainRecord->record_date->format('Y-m-d') }} 至今
                        </div>
                    </div>
                    
                    <div class="relative">
                        <canvas id="rankingChart" height="100"></canvas>
                    </div>
                </div>
            </div>

            <!-- 数据表格 -->
            <div class="mt-8 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">详细数据</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">日期</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">排名</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">变化</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach(array_reverse($rankingData) as $index => $data)
                                    @php
                                        $prevRank = $index < count($rankingData) - 1 ? $rankingData[count($rankingData) - 1 - $index - 1]['rank'] : null;
                                        $rankChange = $prevRank ? $prevRank - $data['rank'] : 0;
                                    @endphp
                                    <tr class="{{ $index % 2 === 0 ? 'bg-white dark:bg-gray-800' : 'bg-gray-50 dark:bg-gray-700' }}">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                            {{ \Carbon\Carbon::parse($data['date'])->format('Y-m-d') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                            #{{ number_format($data['rank']) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            @if($rankChange > 0)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                                    ↑ {{ number_format($rankChange) }}
                                                </span>
                                            @elseif($rankChange < 0)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                                    ↓ {{ number_format(abs($rankChange)) }}
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200">
                                                    - 无变化
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('rankingChart').getContext('2d');
            
            // 准备图表数据
            const rankingData = @json($domainRecord->ranking_data['data'] ?? []);
            const labels = rankingData.map(item => {
                const date = new Date(item.date);
                return date.toLocaleDateString('zh-CN', { month: 'short', day: 'numeric' });
            });
            const data = rankingData.map(item => item.rank);
            
            // 检测暗色模式
            const isDarkMode = document.documentElement.classList.contains('dark');
            const textColor = isDarkMode ? '#e5e7eb' : '#374151';
            const gridColor = isDarkMode ? '#374151' : '#e5e7eb';
            
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: '域名排名',
                        data: data,
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#3b82f6',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 6,
                        pointHoverRadius: 8,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    interaction: {
                        intersect: false,
                        mode: 'index',
                    },
                    plugins: {
                        legend: {
                            display: false,
                        },
                        tooltip: {
                            backgroundColor: isDarkMode ? '#1f2937' : '#ffffff',
                            titleColor: textColor,
                            bodyColor: textColor,
                            borderColor: gridColor,
                            borderWidth: 1,
                            displayColors: false,
                            callbacks: {
                                title: function(context) {
                                    return '日期: ' + rankingData[context[0].dataIndex].date;
                                },
                                label: function(context) {
                                    return '排名: #' + context.parsed.y.toLocaleString();
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                color: gridColor,
                                drawBorder: false,
                            },
                            ticks: {
                                color: textColor,
                                font: {
                                    size: 12
                                }
                            }
                        },
                        y: {
                            reverse: true, // 排名越小越好，所以倒序显示
                            grid: {
                                color: gridColor,
                                drawBorder: false,
                            },
                            ticks: {
                                color: textColor,
                                font: {
                                    size: 12
                                },
                                callback: function(value) {
                                    return '#' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
    @endpush
</x-app-layout>