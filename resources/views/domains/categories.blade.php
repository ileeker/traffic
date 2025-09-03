<x-app-layout>
    {{-- 自定义样式 --}}
    <style>
        .gradient-bg { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .card-hover { transition: all 0.3s ease; }
        .card-hover:hover { transform: translateY(-4px); box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); }
        .stat-card { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
        .stat-card-2 { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
        .stat-card-3 { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); }
        .animate-float { animation: float 6s ease-in-out infinite; }
        @keyframes float { 0%, 100% { transform: translateY(0px); } 50% { transform: translateY(-10px); } }
        .search-glow:focus { box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1), 0 0 20px rgba(99, 102, 241, 0.2); }
        .rank-medal { background: linear-gradient(45deg, #ffd700, #ffed4e); }
        .rank-silver { background: linear-gradient(45deg, #c0c0c0, #e6e6e6); }
        .rank-bronze { background: linear-gradient(45deg, #cd7f32, #daa520); }
        .progress-glow { box-shadow: 0 0 10px rgba(59, 130, 246, 0.5); }
    </style>

    {{-- 渐变背景头部 --}}
    <div class="gradient-bg text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-4xl font-bold tracking-tight">域名分类统计</h1>
                    <p class="text-blue-100 mt-2 text-lg">深入分析您的域名分布情况</p>
                </div>
                <div class="text-right animate-float">
                    <div class="bg-white/20 backdrop-blur-sm rounded-2xl px-6 py-4">
                        <p class="text-blue-100 text-sm font-medium">总域名数量</p>
                        <p class="text-3xl font-bold text-white">{{ number_format($totalDomains) }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 页面主要内容 --}}
    <div class="py-8 bg-gray-50 dark:bg-gray-900 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            
            {{-- 统计卡片 --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="stat-card rounded-2xl p-6 text-white card-hover">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-pink-100 text-sm font-medium">分类总数</p>
                            <p class="text-3xl font-bold mt-2">{{ $categoriesWithTranslation->count() }}</p>
                            <p class="text-pink-200 text-sm mt-1">覆盖所有域名类型</p>
                        </div>
                        <div class="text-4xl opacity-80">📊</div>
                    </div>
                </div>

                <div class="stat-card-2 rounded-2xl p-6 text-white card-hover">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-100 text-sm font-medium">已分类域名</p>
                            <p class="text-3xl font-bold mt-2">{{ number_format($categoriesWithTranslation->sum('count')) }}</p>
                            <p class="text-blue-200 text-sm mt-1">{{ number_format(($categoriesWithTranslation->sum('count') / $totalDomains) * 100, 1) }}% 覆盖率</p>
                        </div>
                        <div class="text-4xl opacity-80">🌐</div>
                    </div>
                </div>

                <div class="stat-card-3 rounded-2xl p-6 text-white card-hover">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-green-100 text-sm font-medium">平均每分类</p>
                            <p class="text-3xl font-bold mt-2">{{ number_format($categoriesWithTranslation->avg('count'), 0) }}</p>
                            <p class="text-green-200 text-sm mt-1">域名数量</p>
                        </div>
                        <div class="text-4xl opacity-80">📈</div>
                    </div>
                </div>
            </div>

            {{-- 搜索和筛选 --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-6">
                <div class="flex flex-col lg:flex-row justify-between items-center space-y-4 lg:space-y-0 lg:space-x-6">
                    <div class="w-full lg:w-1/2">
                        <div class="relative">
                            <input type="text" 
                                id="categorySearch" 
                                placeholder="🔍 搜索分类名称..." 
                                class="w-full pl-12 pr-4 py-4 text-base rounded-xl border-2 border-gray-200 dark:border-gray-600 
                                    focus:border-indigo-500 dark:focus:border-indigo-400 search-glow 
                                    dark:bg-gray-700 dark:text-gray-300 dark:placeholder-gray-400 transition-all duration-300">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center space-x-4">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">排序方式:</label>
                        <select id="sortSelect" 
                            class="rounded-xl border-2 border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white 
                                px-4 py-3 text-sm focus:border-indigo-500 transition-all duration-300">
                            <option value="count-desc">🔢 域名数量 ↓</option>
                            <option value="count-asc">🔢 域名数量 ↑</option>
                            <option value="name-asc">🔤 分类名称 A-Z</option>
                            <option value="name-desc">🔤 分类名称 Z-A</option>
                        </select>
                    </div>
                </div>

                {{-- 筛选统计 --}}
                <div id="filterStats" class="mt-6 hidden">
                    <div class="flex items-center justify-between p-4 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-xl border border-blue-200 dark:border-blue-700">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center mr-3">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                                </svg>
                            </div>
                            <span class="text-gray-700 dark:text-gray-300 font-medium">
                                找到 <span id="filteredCount" class="font-bold text-blue-600 dark:text-blue-400">0</span> 个匹配的分类
                            </span>
                        </div>
                        <button id="clearSearch" 
                            class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg transition-colors duration-300 text-sm font-medium">
                            ✖ 清除搜索
                        </button>
                    </div>
                </div>
            </div>

            {{-- 分类列表表格 --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl overflow-hidden">
                <div class="p-6">
                    <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200 mb-6 flex items-center">
                        <span class="text-2xl mr-3">📋</span>
                        分类详细列表
                    </h2>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead>
                                <tr class="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-700 dark:to-gray-600">
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider rounded-l-lg">排名</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">英文分类</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">中文分类</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">域名数量</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">占比</th>
                                    <th class="px-6 py-4 text-center text-sm font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider rounded-r-lg">操作</th>
                                </tr>
                            </thead>
                            <tbody id="categoryTableBody" class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($categoriesWithTranslation as $index => $categoryData)
                                <tr class="category-row hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-300" 
                                    data-count="{{ $categoryData->count }}"
                                    data-name-en="{{ $categoryData->category }}"
                                    data-name-cn="{{ $categoryData->chinese_name }}"
                                    data-original-index="{{ $index }}">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            @if($index == 0)
                                                <span class="w-10 h-10 rank-medal rounded-full flex items-center justify-center font-bold text-sm shadow-lg">🥇</span>
                                            @elseif($index == 1)
                                                <span class="w-10 h-10 rank-silver rounded-full flex items-center justify-center font-bold text-sm shadow-lg">🥈</span>
                                            @elseif($index == 2)
                                                <span class="w-10 h-10 rank-bronze rounded-full flex items-center justify-center font-bold text-sm shadow-lg">🥉</span>
                                            @else
                                                <span class="w-10 h-10 rounded-full bg-gradient-to-br from-gray-200 to-gray-300 dark:from-gray-600 dark:to-gray-700 text-gray-700 dark:text-gray-300 flex items-center justify-center font-semibold text-sm shadow-md">{{ $index + 1 }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <a href="{{ route('domains.category.domains', ['category' => $categoryData->url_category]) }}" 
                                            class="text-lg font-semibold text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-200 transition-colors duration-300 hover:underline">
                                            {{ $categoryData->category }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4">
                                        <a href="{{ route('domains.category.domains', ['category' => $categoryData->url_category]) }}" 
                                            class="text-lg font-semibold text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-200 transition-colors duration-300 hover:underline">
                                            {{ $categoryData->chinese_name }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold 
                                            @if($categoryData->count > 10000) bg-gradient-to-r from-green-400 to-green-600 text-white
                                            @elseif($categoryData->count > 5000) bg-gradient-to-r from-blue-400 to-blue-600 text-white
                                            @else bg-gradient-to-r from-gray-400 to-gray-600 text-white
                                            @endif shadow-md">
                                            {{ number_format($categoryData->count) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center space-x-3">
                                            <span class="text-sm font-semibold text-gray-900 dark:text-gray-100 min-w-0 flex-shrink-0">
                                                {{ number_format(($categoryData->count / $totalDomains) * 100, 2) }}%
                                            </span>
                                            <div class="flex-1 max-w-xs">
                                                <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-3 overflow-hidden">
                                                    <div class="bg-gradient-to-r from-blue-400 to-purple-600 h-3 rounded-full transition-all duration-500 progress-glow" 
                                                        style="width: {{ ($categoryData->count / $categoriesWithTranslation->max('count')) * 100 }}%"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <a href="{{ route('domains.category.domains', ['category' => $categoryData->url_category]) }}" 
                                            class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white text-sm font-medium rounded-lg transition-all duration-300 shadow-md hover:shadow-lg transform hover:scale-105">
                                            查看域名
                                            <svg class="ml-2 -mr-0.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                            </svg>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- 没有找到结果的提示 --}}
                    <div id="noResultsMessage" class="hidden">
                        <div class="text-center py-16">
                            <div class="text-6xl mb-4">🔍</div>
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-2">未找到匹配的分类</h3>
                            <p class="text-gray-500 dark:text-gray-400">请尝试使用不同的关键词搜索</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 图表展示 --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-6">
                <h3 class="text-2xl font-bold text-gray-800 dark:text-gray-200 mb-6 flex items-center">
                    <span class="text-2xl mr-3">📊</span>
                    Top 10 分类分布图表
                </h3>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-xl p-4">
                        <h4 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-4 text-center">分类占比分布</h4>
                        <canvas id="categoryPieChart" class="max-h-80"></canvas>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-xl p-4">
                        <h4 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-4 text-center">域名数量对比</h4>
                        <canvas id="categoryBarChart" class="max-h-80"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- JavaScript 功能 --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('categorySearch');
            const sortSelect = document.getElementById('sortSelect');
            const tableBody = document.getElementById('categoryTableBody');
            const noResultsMessage = document.getElementById('noResultsMessage');
            const filterStats = document.getElementById('filterStats');
            const filteredCountSpan = document.getElementById('filteredCount');
            const clearSearchBtn = document.getElementById('clearSearch');
            let rows = Array.from(tableBody.querySelectorAll('.category-row'));

            // 保存原始顺序
            const originalOrder = [...rows];

            // 更新排名显示
            function updateRankDisplay() {
                const visibleRows = rows.filter(row => row.style.display !== 'none');
                visibleRows.forEach((row, index) => {
                    const rankCell = row.cells[0];
                    let rankDisplay = '';
                    
                    if (index === 0) {
                        rankDisplay = '<span class="w-10 h-10 rank-medal rounded-full flex items-center justify-center font-bold text-sm shadow-lg">🥇</span>';
                    } else if (index === 1) {
                        rankDisplay = '<span class="w-10 h-10 rank-silver rounded-full flex items-center justify-center font-bold text-sm shadow-lg">🥈</span>';
                    } else if (index === 2) {
                        rankDisplay = '<span class="w-10 h-10 rank-bronze rounded-full flex items-center justify-center font-bold text-sm shadow-lg">🥉</span>';
                    } else {
                        rankDisplay = `<span class="w-10 h-10 rounded-full bg-gradient-to-br from-gray-200 to-gray-300 dark:from-gray-600 dark:to-gray-700 text-gray-700 dark:text-gray-300 flex items-center justify-center font-semibold text-sm shadow-md">${index + 1}</span>`;
                    }
                    
                    rankCell.innerHTML = `<div class="flex items-center">${rankDisplay}</div>`;
                });
            }

            // 搜索功能
            function performSearch() {
                const searchTerm = searchInput.value.toLowerCase().trim();
                let visibleRows = 0;

                rows.forEach(function(row) {
                    const englishCategory = row.dataset.nameEn.toLowerCase();
                    const chineseCategory = row.dataset.nameCn.toLowerCase();
                    
                    if (searchTerm === '' || englishCategory.includes(searchTerm) || chineseCategory.includes(searchTerm)) {
                        row.style.display = '';
                        visibleRows++;
                    } else {
                        row.style.display = 'none';
                    }
                });

                if (searchTerm !== '') {
                    filterStats.classList.remove('hidden');
                    filteredCountSpan.textContent = visibleRows;
                } else {
                    filterStats.classList.add('hidden');
                }

                if (visibleRows === 0 && searchTerm !== '') {
                    noResultsMessage.classList.remove('hidden');
                    tableBody.style.display = 'none';
                } else {
                    noResultsMessage.classList.add('hidden');
                    tableBody.style.display = '';
                }

                updateRankDisplay();
            }

            // 排序功能
            function performSort() {
                const [sortField, sortOrder] = sortSelect.value.split('-');
                let sortedRows = [...rows];

                sortedRows.sort((a, b) => {
                    let aValue, bValue;

                    if (sortField === 'count') {
                        aValue = parseInt(a.dataset.count);
                        bValue = parseInt(b.dataset.count);
                    } else if (sortField === 'name') {
                        aValue = a.dataset.nameEn.toLowerCase();
                        bValue = b.dataset.nameEn.toLowerCase();
                    }

                    if (sortOrder === 'asc') {
                        return aValue > bValue ? 1 : -1;
                    } else {
                        return aValue < bValue ? 1 : -1;
                    }
                });

                // 重新排列DOM
                tableBody.innerHTML = '';
                sortedRows.forEach(row => {
                    tableBody.appendChild(row);
                });
                rows = sortedRows;

                performSearch(); // 重新应用搜索
            }

            // 清除搜索
            function clearSearch() {
                searchInput.value = '';
                filterStats.classList.add('hidden');
                noResultsMessage.classList.add('hidden');
                tableBody.style.display = '';
                
                // 恢复原始顺序
                tableBody.innerHTML = '';
                originalOrder.forEach(row => {
                    row.style.display = '';
                    tableBody.appendChild(row);
                });
                rows = [...originalOrder];
                
                sortSelect.value = 'count-desc';
                updateRankDisplay();
            }

            // 事件监听器
            searchInput.addEventListener('input', performSearch);
            sortSelect.addEventListener('change', performSort);
            clearSearchBtn.addEventListener('click', clearSearch);

            searchInput.addEventListener('keyup', function(e) {
                if (e.key === 'Escape') {
                    clearSearch();
                }
            });

            // 初始化图表
            const topCategories = @json($categoriesWithTranslation->take(10));
            
            // 饼图
            const pieCtx = document.getElementById('categoryPieChart').getContext('2d');
            new Chart(pieCtx, {
                type: 'doughnut',
                data: {
                    labels: topCategories.map(c => c.chinese_name),
                    datasets: [{
                        data: topCategories.map(c => c.count),
                        backgroundColor: [
                            '#FF6B6B', '#4ECDC4', '#45B7D1', '#FFA07A', '#98D8C8',
                            '#F7DC6F', '#BB8FCE', '#85C1E9', '#F8C471', '#82E0AA'
                        ],
                        borderWidth: 3,
                        borderColor: '#fff',
                        hoverBorderWidth: 5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 20,
                                font: {
                                    size: 13,
                                    family: 'Inter, system-ui, -apple-system, sans-serif'
                                },
                                usePointStyle: true,
                                pointStyle: 'circle'
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            borderColor: '#666',
                            borderWidth: 1,
                            cornerRadius: 8,
                            callbacks: {
                                label: function(context) {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = ((context.parsed / total) * 100).toFixed(1);
                                    return context.label + ': ' + context.parsed.toLocaleString() + ' (' + percentage + '%)';
                                }
                            }
                        }
                    },
                    cutout: '60%'
                }
            });

            // 条形图
            const barCtx = document.getElementById('categoryBarChart').getContext('2d');
            new Chart(barCtx, {
                type: 'bar',
                data: {
                    labels: topCategories.map(c => c.chinese_name),
                    datasets: [{
                        label: '域名数量',
                        data: topCategories.map(c => c.count),
                        backgroundColor: [
                            '#FF6B6B', '#4ECDC4', '#45B7D1', '#FFA07A', '#98D8C8',
                            '#F7DC6F', '#BB8FCE', '#85C1E9', '#F8C471', '#82E0AA'
                        ],
                        borderColor: '#fff',
                        borderWidth: 2,
                        borderRadius: 8,
                        borderSkipped: false
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            borderColor: '#666',
                            borderWidth: 1,
                            cornerRadius: 8
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.1)'
                            },
                            ticks: {
                                callback: function(value) {
                                    return value.toLocaleString();
                                },
                                font: {
                                    family: 'Inter, system-ui, -apple-system, sans-serif'
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                maxRotation: 45,
                                minRotation: 45,
                                font: {
                                    family: 'Inter, system-ui, -apple-system, sans-serif'
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
</x-app-layout>