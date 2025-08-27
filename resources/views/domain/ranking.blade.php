{{--
    This Blade template displays the ranking trend for a specific domain.

    It expects the following properties on the $domainRecord object:
    - domain (string): The domain name.
    - last_updated (Carbon instance): The timestamp of the last update.
    - current_ranking (int): The latest ranking number.
    - record_date (Carbon instance): The date when recording started.
    - ranking_data (array): An array containing the historical ranking data.
      - e.g., ['data' => [['date' => '2023-10-26', 'rank' => 150], ...]]

    For even cleaner code, consider moving the ranking calculation logic
    from the Blade template into Accessors on your DomainRecord model.
    For example:
    - public function getRankingDataArrayAttribute(): array
    - public function getOverallRankingChangeAttribute(): int
    - public function getRankingRecordDurationInDaysAttribute(): int
--}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    域名排名趋势
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $domainRecord->domain }}</p>
            </div>
            <div class="text-sm text-gray-600 dark:text-gray-400">
                最后更新: {{ $domainRecord->last_updated->format('Y-m-d H:i:s') }}
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Pre-calculate ranking statistics to keep the HTML clean --}}
            @php
                $rankingData = $domainRecord->ranking_data['data'] ?? [];
                $firstRank = $rankingData[0]['rank'] ?? $domainRecord->current_ranking;
                $overallChange = $firstRank - $domainRecord->current_ranking;
                $changeIsPositive = $overallChange > 0;
                $changeIsNegative = $overallChange < 0;
                $recordDuration = count($rankingData);
            @endphp

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-blue-100 dark:bg-blue-800/30 rounded-full p-3">
                            <svg class="w-6 h-6 text-blue-500 dark:text-blue-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">当前排名</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                #{{ number_format($domainRecord->current_ranking) }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center">
                        <div @class([
                            'flex-shrink-0 rounded-full p-3',
                            'bg-green-100 dark:bg-green-800/30' => $changeIsPositive,
                            'bg-red-100 dark:bg-red-800/30' => $changeIsNegative,
                            'bg-gray-100 dark:bg-gray-700' => !$changeIsPositive && !$changeIsNegative,
                        ])>
                            <svg @class([
                                'w-6 h-6',
                                'text-green-500 dark:text-green-400' => $changeIsPositive,
                                'text-red-500 dark:text-red-400' => $changeIsNegative,
                                'text-gray-500 dark:text-gray-400' => !$changeIsPositive && !$changeIsNegative,
                            ]) xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                @if ($changeIsPositive)
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-3.75-.625m3.75.625V3.375" />
                                @else
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6L9 12.75l4.286-4.286a11.948 11.948 0 014.306 6.43l.776 2.898m0 0l3.182-5.511m-3.182 5.51L11 21" />
                                @endif
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">排名变化 (总)</p>
                            <p @class([
                                'text-2xl font-bold',
                                'text-green-600 dark:text-green-400' => $changeIsPositive,
                                'text-red-600 dark:text-red-400' => $changeIsNegative,
                                'text-gray-900 dark:text-white' => !$changeIsPositive && !$changeIsNegative,
                            ])>
                                {{ $overallChange > 0 ? '↑' : ($overallChange < 0 ? '↓' : '') }}{{ number_format(abs($overallChange)) }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-purple-100 dark:bg-purple-800/30 rounded-full p-3">
                            <svg class="w-6 h-6 text-purple-500 dark:text-purple-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0h18" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">记录天数</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                {{ $recordDuration }} 天
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex flex-wrap justify-between items-start gap-4 mb-6">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">排名趋势图</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                记录始于 {{ $domainRecord->record_date->format('Y-m-d') }}
                            </p>
                        </div>
                    </div>
                    <div class="h-80">
                        <canvas id="rankingChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="mt-8 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">详细数据</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700/50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">日期</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">排名</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">每日变化</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @php
                                    $reversedData = array_reverse($rankingData);
                                @endphp
                                @forelse($reversedData as $index => $data)
                                    @php
                                        $previousDayData = $reversedData[$index + 1] ?? null;
                                        $dailyChange = $previousDayData ? $previousDayData['rank'] - $data['rank'] : 0;
                                    @endphp
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-150">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                            {{ \Carbon\Carbon::parse($data['date'])->format('Y-m-d') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                            #{{ number_format($data['rank']) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            @if($dailyChange > 0)
                                                <span class="inline-flex items-center gap-x-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                                    <svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 17a.75.75 0 01-.75-.75V5.612L5.03 9.83a.75.75 0 01-1.06-1.06l5.25-5.25a.75.75 0 011.06 0l5.25 5.25a.75.75 0 11-1.06 1.06L10.75 5.612V16.25A.75.75 0 0110 17z" clip-rule="evenodd" /></svg>
                                                    {{ number_format($dailyChange) }}
                                                </span>
                                            @elseif($dailyChange < 0)
                                                <span class="inline-flex items-center gap-x-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                                    <svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a.75.75 0 01.75.75v10.638l4.22-4.22a.75.75 0 111.06 1.06l-5.25 5.25a.75.75 0 01-1.06 0l-5.25-5.25a.75.75 0 111.06-1.06L9.25 14.388V3.75A.75.75 0 0110 3z" clip-rule="evenodd" /></svg>
                                                    {{ number_format(abs($dailyChange)) }}
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium text-gray-500 dark:text-gray-400">
                                                    -
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                                            暂无详细排名数据
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    {{-- It's often better to include Chart.js via npm, but CDN is fine for simplicity. --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Ensure ranking data exists
            const rankingData = @json($domainRecord->ranking_data['data'] ?? []);
            if (!rankingData || rankingData.length === 0) {
                return;
            }

            // Prepare data for the chart
            const labels = rankingData.map(item => {
                const date = new Date(item.date);
                // Format date as 'M月d日' for Chinese locale
                return date.toLocaleDateString('zh-CN', { month: 'short', day: 'numeric' });
            });
            const dataPoints = rankingData.map(item => item.rank);

            // Check for dark mode to set colors dynamically
            const isDarkMode = document.documentElement.classList.contains('dark');
            const textColor = isDarkMode ? 'rgba(229, 231, 235, 0.8)' : '#4b5563';
            const gridColor = isDarkMode ? 'rgba(55, 65, 81, 0.8)' : 'rgba(229, 231, 235, 0.8)';
            const tooltipBackgroundColor = isDarkMode ? '#1f2937' : '#ffffff';

            // Get chart canvas context
            const ctx = document.getElementById('rankingChart').getContext('2d');
            
            // Create a gradient fill for the line chart area
            const gradient = ctx.createLinearGradient(0, 0, 0, 320); // Height of the chart area
            gradient.addColorStop(0, 'rgba(59, 130, 246, 0.4)');
            gradient.addColorStop(1, 'rgba(59, 130, 246, 0)');


            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: '域名排名',
                        data: dataPoints,
                        borderColor: '#3b82f6',
                        backgroundColor: gradient,
                        borderWidth: 2.5,
                        fill: true,
                        tension: 0.4, // Makes the line smooth
                        pointBackgroundColor: '#3b82f6',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 5,
                        pointHoverRadius: 7,
                        pointHoverBorderWidth: 2,
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
                            display: false, // Legend is redundant here
                        },
                        tooltip: {
                            backgroundColor: tooltipBackgroundColor,
                            titleColor: textColor,
                            bodyColor: textColor,
                            borderColor: gridColor,
                            borderWidth: 1,
                            displayColors: false, // Hides the color box in tooltip
                            padding: 10,
                            callbacks: {
                                // Show full date in tooltip title
                                title: (context) => '日期: ' + rankingData[context[0].dataIndex].date,
                                // Format the ranking number
                                label: (context) => '排名: #' + context.parsed.y.toLocaleString(),
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false, // Hide vertical grid lines for a cleaner look
                            },
                            ticks: {
                                color: textColor,
                                font: { size: 12 }
                            }
                        },
                        y: {
                            reverse: true, // Lower rank number is better, so reverse the axis
                            grid: {
                                color: gridColor,
                                drawBorder: false,
                            },
                            ticks: {
                                color: textColor,
                                font: { size: 12 },
                                // Add '#' prefix to axis labels
                                callback: (value) => '#' + Number(value).toLocaleString()
                            }
                        }
                    }
                }
            });
        });
    </script>
    @endpush
</x-app-layout>