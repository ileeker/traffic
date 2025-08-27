<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Domain Ranking') }} - {{ $domain->name ?? 'Unknown' }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- 域名基本信息卡片 --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-semibold mb-4">Domain Information</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                            <h4 class="font-medium text-gray-600 dark:text-gray-300">Domain Name</h4>
                            <p class="text-lg font-semibold">{{ $domain->name ?? 'N/A' }}</p>
                        </div>
                        
                        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                            <h4 class="font-medium text-gray-600 dark:text-gray-300">Status</h4>
                            <p class="text-lg font-semibold">
                                <span class="px-2 py-1 rounded-full text-xs 
                                    {{ ($domain->status ?? '') === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                    {{ $domain->status ?? 'Unknown' }}
                                </span>
                            </p>
                        </div>
                        
                        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                            <h4 class="font-medium text-gray-600 dark:text-gray-300">Category</h4>
                            <p class="text-lg font-semibold">{{ $domain->category ?? 'N/A' }}</p>
                        </div>
                        
                        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                            <h4 class="font-medium text-gray-600 dark:text-gray-300">Last Updated</h4>
                            <p class="text-lg font-semibold">
                                {{ $domain->updated_at ? $domain->updated_at->format('Y-m-d') : 'N/A' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 排名图表卡片 --}}
            @if($rankingData && isset($rankingData['data']) && count($rankingData['data']) > 0)
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-semibold mb-4">Ranking Trend</h3>
                    
                    {{-- 图表容器 --}}
                    <div class="w-full h-96">
                        <canvas id="rankingChart"></canvas>
                    </div>
                </div>
            </div>
            @else
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="text-center py-8">
                        <div class="text-gray-400 dark:text-gray-600">
                            <svg class="mx-auto h-12 w-12 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">No Ranking Data Available</h3>
                        <p class="text-gray-500 dark:text-gray-400 mt-2">
                            There is no ranking data available for this domain yet.
                        </p>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- Chart.js 脚本 --}}
    @if($rankingData && isset($rankingData['data']) && count($rankingData['data']) > 0)
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 解析PHP数据到JavaScript
            const rankingData = @json($rankingData['data']);
            
            // 准备图表数据
            const labels = rankingData.map(item => item.date);
            const ranks = rankingData.map(item => item.rank);
            
            // 配置图表
            const ctx = document.getElementById('rankingChart').getContext('2d');
            const chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Domain Ranking',
                        data: ranks,
                        borderColor: '#3B82F6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#3B82F6',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 5,
                        pointHoverRadius: 7
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            labels: {
                                usePointStyle: true,
                                padding: 20
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleColor: '#ffffff',
                            bodyColor: '#ffffff',
                            borderColor: '#3B82F6',
                            borderWidth: 1,
                            displayColors: false,
                            callbacks: {
                                title: function(context) {
                                    return 'Date: ' + context[0].label;
                                },
                                label: function(context) {
                                    return 'Ranking: #' + context.parsed.y;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            display: true,
                            title: {
                                display: true,
                                text: 'Date',
                                font: {
                                    weight: 'bold'
                                }
                            },
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            display: true,
                            title: {
                                display: true,
                                text: 'Ranking',
                                font: {
                                    weight: 'bold'
                                }
                            },
                            reverse: true, // 排名越小越好，所以反转Y轴
                            beginAtZero: false,
                            grid: {
                                color: 'rgba(156, 163, 175, 0.1)'
                            },
                            ticks: {
                                callback: function(value) {
                                    return '#' + value;
                                }
                            }
                        }
                    },
                    elements: {
                        point: {
                            hoverBorderWidth: 3
                        }
                    }
                }
            });
        });
    </script>
    @endif
</x-app-layout>