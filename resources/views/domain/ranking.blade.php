<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                域名综合分析 - {{ $domainRecord ? $domainRecord->domain : ($similarwebRecord ? $similarwebRecord->domain : '') }}
            </h2>
            <div class="text-sm text-gray-600 dark:text-gray-400">
                @if($domainRecord)
                    排名更新: {{ $domainRecord->last_updated->format('Y-m-d H:i:s') }}
                @endif
                @if($similarwebRecord)
                    <br>流量更新: {{ $similarwebRecord->last_updated->format('Y-m-d H:i:s') }}
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            @if($domainRecord)
                @php
                    $rankingData = $domainRecord->ranking_data['data'] ?? [];
                    $firstRank = count($rankingData) > 0 ? $rankingData[0]['rank'] : $domainRecord->current_ranking;
                    $lastRank = $domainRecord->current_ranking;
                    $change = $firstRank - $lastRank;
                @endphp

                <!-- 排名统计信息 -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">排名数据</h3>
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

                <!-- 排名趋势图 -->
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
            @endif

            @if($similarwebRecord)
                @php
                    $trafficData = $similarwebRecord->traffic_data['data'] ?? [];
                    $topCountries = $similarwebRecord->top_country_shares ?? [];
                @endphp

                <!-- Similarweb 基本信息 -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">网站信息</h3>
                            @if($similarwebRecord->title)
                                <span class="text-sm text-gray-600 dark:text-gray-400">{{ $similarwebRecord->title }}</span>
                            @endif
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                            <!-- 全球排名 -->
                            <div class="text-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <p class="text-xs text-gray-600 dark:text-gray-400">全球排名</p>
                                <p class="text-xl font-bold text-gray-900 dark:text-white">
                                    #{{ number_format($similarwebRecord->global_rank) }}
                                </p>
                            </div>
                            
                            <!-- 分类排名 -->
                            <div class="text-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <p class="text-xs text-gray-600 dark:text-gray-400">分类排名</p>
                                <p class="text-xl font-bold text-gray-900 dark:text-white">
                                    #{{ number_format($similarwebRecord->category_rank) }}
                                </p>
                            </div>

                            <!-- 月访问量 -->
                            <div class="text-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <p class="text-xs text-gray-600 dark:text-gray-400">月访问量</p>
                                <p class="text-xl font-bold text-gray-900 dark:text-white">
                                    {{ number_format($similarwebRecord->current_emv) }}
                                </p>
                            </div>

                            <!-- 跳出率 -->
                            <div class="text-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <p class="text-xs text-gray-600 dark:text-gray-400">跳出率</p>
                                <p class="text-xl font-bold text-gray-900 dark:text-white">
                                    {{ number_format($similarwebRecord->current_bounce_rate * 100, 1) }}%
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 流量来源 -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">流量来源</h3>
                        
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                            <div class="text-center p-4 border dark:border-gray-600 rounded-lg">
                                <p class="text-sm text-gray-600 dark:text-gray-400">直接访问</p>
                                <p class="text-lg font-bold text-blue-600">{{ number_format($similarwebRecord->ts_direct * 100, 1) }}%</p>
                            </div>
                            <div class="text-center p-4 border dark:border-gray-600 rounded-lg">
                                <p class="text-sm text-gray-600 dark:text-gray-400">搜索引擎</p>
                                <p class="text-lg font-bold text-green-600">{{ number_format($similarwebRecord->ts_search * 100, 1) }}%</p>
                            </div>
                            <div class="text-center p-4 border dark:border-gray-600 rounded-lg">
                                <p class="text-sm text-gray-600 dark:text-gray-400">推荐链接</p>
                                <p class="text-lg font-bold text-purple-600">{{ number_format($similarwebRecord->ts_referrals * 100, 1) }}%</p>
                            </div>
                            <div class="text-center p-4 border dark:border-gray-600 rounded-lg">
                                <p class="text-sm text-gray-600 dark:text-gray-400">社交媒体</p>
                                <p class="text-lg font-bold text-pink-600">{{ number_format($similarwebRecord->ts_social * 100, 1) }}%</p>
                            </div>
                            <div class="text-center p-4 border dark:border-gray-600 rounded-lg">
                                <p class="text-sm text-gray-600 dark:text-gray-400">付费推广</p>
                                <p class="text-lg font-bold text-orange-600">{{ number_format($similarwebRecord->ts_paid_referrals * 100, 1) }}%</p>
                            </div>
                            <div class="text-center p-4 border dark:border-gray-600 rounded-lg">
                                <p class="text-sm text-gray-600 dark:text-gray-400">邮件</p>
                                <p class="text-lg font-bold text-red-600">{{ number_format($similarwebRecord->ts_mail * 100, 1) }}%</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 流量趋势图 -->
                @if(count($trafficData) > 0)
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">流量趋势图</h3>
                        </div>
                        
                        <div class="relative" style="height: 400px;">
                            <canvas id="trafficChart"></canvas>
                        </div>
                    </div>
                </div>
                @endif

                <!-- 地理分布 -->
                @if(count($topCountries) > 0)
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">地理分布 (前5名)</h3>
                        
                        <div class="space-y-4">
                            @foreach(array_slice($topCountries, 0, 5) as $country)
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <span class="text-2xl mr-3">{{ $country['CountryCode'] === 'DE' ? '🇩🇪' : ($country['CountryCode'] === 'DK' ? '🇩🇰' : ($country['CountryCode'] === 'ES' ? '🇪🇸' : ($country['CountryCode'] === 'FR' ? '🇫🇷' : '🇸🇪'))) }}</span>
                                        <span class="font-medium text-gray-900 dark:text-white">{{ $country['CountryCode'] }}</span>
                                    </div>
                                    <div class="flex items-center space-x-4 flex-1 ml-6">
                                        <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                            <div class="bg-blue-500 h-2 rounded-full" style="width: {{ $country['Value'] * 100 }}%"></div>
                                        </div>
                                        <span class="text-sm font-medium text-gray-900 dark:text-white min-w-12">
                                            {{ number_format($country['Value'] * 100, 1) }}%
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif
            @endif
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script>
        window.addEventListener('load', function() {
            setTimeout(function() {
                if (typeof Chart === 'undefined') {
                    console.error('Chart.js 未正确加载');
                    return;
                }

                // 检测暗色模式
                const isDarkMode = document.documentElement.classList.contains('dark') || 
                                  window.matchMedia('(prefers-color-scheme: dark)').matches;
                const textColor = isDarkMode ? '#e5e7eb' : '#374151';
                const gridColor = isDarkMode ? '#374151' : '#e5e7eb';

                // 排名趋势图
                @if($domainRecord)
                const rankingCtx = document.getElementById('rankingChart');
                if (rankingCtx) {
                    const rankingData = @json($domainRecord->ranking_data['data'] ?? []);
                    
                    if (rankingData && rankingData.length > 0) {
                        const rankingLabels = rankingData.map(item => {
                            const date = new Date(item.date);
                            return date.toLocaleDateString('zh-CN', { month: 'short', day: 'numeric' });
                        });
                        const rankingValues = rankingData.map(item => item.rank);
                        
                        new Chart(rankingCtx, {
                            type: 'line',
                            data: {
                                labels: rankingLabels,
                                datasets: [{
                                    label: '域名排名',
                                    data: rankingValues,
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
                                    legend: { display: false },
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
                                        grid: { color: gridColor, drawBorder: false },
                                        ticks: { color: textColor, font: { size: 12 } }
                                    },
                                    y: {
                                        reverse: true,
                                        grid: { color: gridColor, drawBorder: false },
                                        ticks: {
                                            color: textColor,
                                            font: { size: 12 },
                                            callback: function(value) {
                                                return '#' + value.toLocaleString();
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    } else {
                        rankingCtx.parentElement.innerHTML = '<div class="text-center p-8 text-gray-500">暂无排名数据</div>';
                    }
                }
                @endif

                // 流量趋势图
                @if($similarwebRecord)
                const trafficCtx = document.getElementById('trafficChart');
                if (trafficCtx) {
                    const trafficData = @json($similarwebRecord->traffic_data['data'] ?? []);
                    
                    if (trafficData && trafficData.length > 0) {
                        const trafficLabels = trafficData.map(item => {
                            const date = new Date(item.month + '-01');
                            return date.toLocaleDateString('zh-CN', { year: 'numeric', month: 'short' });
                        });
                        const trafficValues = trafficData.map(item => item.emv);
                        
                        new Chart(trafficCtx, {
                            type: 'line',
                            data: {
                                labels: trafficLabels,
                                datasets: [{
                                    label: '月访问量',
                                    data: trafficValues,
                                    borderColor: '#10b981',
                                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                                    borderWidth: 3,
                                    fill: true,
                                    tension: 0.4,
                                    pointBackgroundColor: '#10b981',
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
                                    legend: { display: false },
                                    tooltip: {
                                        backgroundColor: isDarkMode ? '#1f2937' : '#ffffff',
                                        titleColor: textColor,
                                        bodyColor: textColor,
                                        borderColor: gridColor,
                                        borderWidth: 1,
                                        displayColors: false,
                                        callbacks: {
                                            title: function(context) {
                                                return '月份: ' + trafficData[context[0].dataIndex].month;
                                            },
                                            label: function(context) {
                                                return '访问量: ' + context.parsed.y.toLocaleString();
                                            }
                                        }
                                    }
                                },
                                scales: {
                                    x: {
                                        grid: { color: gridColor, drawBorder: false },
                                        ticks: { color: textColor, font: { size: 12 } }
                                    },
                                    y: {
                                        grid: { color: gridColor, drawBorder: false },
                                        ticks: {
                                            color: textColor,
                                            font: { size: 12 },
                                            callback: function(value) {
                                                return value.toLocaleString();
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    } else {
                        trafficCtx.parentElement.innerHTML = '<div class="text-center p-8 text-gray-500">暂无流量数据</div>';
                    }
                }
                @endif
            }, 100);
        });
    </script>
</x-app-layout>