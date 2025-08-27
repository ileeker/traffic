{{-- 继承自 app.blade.php 布局 --}}
<x-app-layout>
    {{-- 页面标题 --}}
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('域名排名详情: ') }} {{ $domainInfo->domain }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    
                    {{-- 基础信息展示 --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg shadow">
                            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">域名</h3>
                            <p class="mt-1 text-2xl font-semibold">{{ $domainInfo->domain }}</p>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg shadow">
                            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">当前排名</h3>
                            <p class="mt-1 text-2xl font-semibold text-indigo-500">{{ number_format($domainInfo->current_ranking) }}</p>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg shadow">
                            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">最后更新时间</h3>
                            <p class="mt-1 text-xl font-semibold">{{ $domainInfo->last_updated->format('Y-m-d H:i') }}</p>
                        </div>
                    </div>

                    {{-- 图表容器 --}}
                    <div class="mt-8">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100 mb-4">
                            排名变化历史
                        </h3>
                        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg shadow">
                            {{-- 画布元素，图表将在这里渲染 --}}
                            <canvas id="rankingChart"></canvas>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- 引入 Chart.js 库 --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    {{-- 在脚本之前准备好数据，避免在 @json 中使用复杂表达式 --}}
    @php
        $chartData = $domainInfo->ranking_data['data'] ?? [];
    @endphp

    <script>
        // 使用 @json 指令安全地将准备好的 PHP 变量转换为 JavaScript 对象
        const rankingData = @json($chartData);
        
        // 从数据中提取日期作为图表的标签 (X轴)
        const labels = rankingData.map(item => item.date);
        // 从数据中提取排名作为图表的数据点 (Y轴)
        const dataPoints = rankingData.map(item => item.rank);

        // 获取 canvas 元素的上下文
        const ctx = document.getElementById('rankingChart').getContext('2d');
        
        // 创建新的图表实例
        const rankingChart = new Chart(ctx, {
            type: 'line', // 图表类型为折线图
            data: {
                labels: labels,
                datasets: [{
                    label: '排名',
                    data: dataPoints,
                    borderColor: 'rgb(79, 70, 229)', // 线条颜色 (Indigo)
                    backgroundColor: 'rgba(79, 70, 229, 0.1)', // 填充颜色
                    borderWidth: 2,
                    fill: true, // 填充线条下方的区域
                    tension: 0.2 // 使线条稍微弯曲，更平滑
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    y: {
                        // 反转 Y 轴，因为排名数字越小越好
                        reverse: true, 
                        ticks: {
                            color: document.documentElement.classList.contains('dark') ? 'rgba(255,255,255,0.7)' : 'rgba(0,0,0,0.7)',
                            // 格式化刻度标签，例如将 10000 显示为 10k
                            callback: function(value, index, values) {
                                if (value >= 1000) {
                                    return (value / 1000) + 'k';
                                }
                                return value;
                            }
                        },
                        grid: {
                            color: document.documentElement.classList.contains('dark') ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)',
                        }
                    },
                    x: {
                       ticks: {
                            color: document.documentElement.classList.contains('dark') ? 'rgba(255,255,255,0.7)' : 'rgba(0,0,0,0.7)'
                       },
                       grid: {
                            display: false, // 隐藏 X 轴的网格线
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false // 只有一个数据集，所以隐藏图例
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    // 格式化提示框中的数字，添加千位分隔符
                                    label += new Intl.NumberFormat('en-US').format(context.parsed.y);
                                }
                                return label;
                            }
                        }
                    }
                }
            }
        });
    </script>
</x-app-layout>