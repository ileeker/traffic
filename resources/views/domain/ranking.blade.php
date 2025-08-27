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
            @php
                $rankingData = $domainRecord->ranking_data['data'] ?? [];
                $firstRank = count($rankingData) > 0 ? $rankingData[0]['rank'] : $domainRecord->current_ranking;
                $lastRank = $domainRecord->current_ranking;
                $change = $firstRank - $lastRank;
                $changePercent = $firstRank > 0 ? (($change / $firstRank) * 100) : 0;
            @endphp

            <!-- 横排统计信息 -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-8">
                <div class="p-6">
                    <div class="flex flex-wrap justify-between items-center space-y-4 md:space-y-0">
                        <div class="flex items-center space-x-6">
                            <!-- 当前排名 -->
                            <div class="flex items-center">
                                <div class="p-2 bg-blue-500 bg-opacity-10 rounded-full mr-3">
                                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-gray-600 dark:text-gray-400">当前排名</p>
                                    <p class="text-lg font-bold text-gray-900 dark:text-white">
                                        #{{ number_format($domainRecord->current_ranking) }}
                                    </p>
                                </div>
                            </div>

                            <!-- 排名变化 -->
                            <div class="flex items-center">
                                <div class="p-2 {{ $change > 0 ? 'bg-green-500' : 'bg-red-500' }} bg-opacity-10 rounded-full mr-3">
                                    <svg class="w-5 h-5 {{ $change > 0 ? 'text-green-500' : 'text-red-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        @if($change > 0)
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
                                        @else
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                                        @endif
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-gray-600 dark:text-gray-400">排名变化</p>
                                    <p class="text-lg font-bold {{ $change > 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $change > 0 ? '+' : '' }}{{ number_format($change) }}
                                    </p>
                                </div>
                            </div>

                            <!-- 记录天数 -->
                            <div class="flex items-center">
                                <div class="p-2 bg-purple-500 bg-opacity-10 rounded-full mr-3">
                                    <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-gray-600 dark:text-gray-400">记录天数</p>
                                    <p class="text-lg font-bold text-gray-900 dark:text-white">
                                        {{ count($rankingData) }} 天
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- 记录时间范围 -->
                        <div class="text-right">
                            <p class="text-xs text-gray-600 dark:text-gray-400">记录时间</p>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">
                                {{ $domainRecord->record_date->format('Y-m-d') }} 至今
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 图表区域 -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">排名趋势图</h3>
                    </div>
                    
                    <div class="relative" style="height: 400px;">
                        <canvas id="rankingChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script>
        window.addEventListener('load', function() {
            // 等待一小段时间确保 Chart.js 完全加载
            setTimeout(function() {
                if (typeof Chart === 'undefined') {
                    console.error('Chart.js 未正确加载');
                    document.getElementById('rankingChart').parentElement.innerHTML = 
                        '<div class="text-center p-8 text-gray-500">图表加载失败，请刷新页面重试</div>';
                    return;
                }

                const ctx = document.getElementById('rankingChart');
                if (!ctx) {
                    console.error('找不到图表容器');
                    return;
                }
                
                // 准备图表数据
                const rankingData = @json($domainRecord->ranking_data['data'] ?? []);
                console.log('排名数据:', rankingData);
                
                if (!rankingData || rankingData.length === 0) {
                    console.log('没有排名数据');
                    ctx.parentElement.innerHTML = '<div class="text-center p-8 text-gray-500">暂无排名数据</div>';
                    return;
                }
                
                const labels = rankingData.map(item => {
                    const date = new Date(item.date);
                    return date.toLocaleDateString('zh-CN', { month: 'short', day: 'numeric' });
                });
                const data = rankingData.map(item => item.rank);
                
                console.log('图表标签:', labels);
                console.log('图表数据:', data);
                
                // 检测暗色模式
                const isDarkMode = document.documentElement.classList.contains('dark') || 
                                  window.matchMedia('(prefers-color-scheme: dark)').matches;
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
                        maintainAspectRatio: false,
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
            }, 100);
        });
    </script>
    </script>
</x-app-layout>