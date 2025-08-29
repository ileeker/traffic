<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('域名排名变化数据') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <!-- 统计信息卡片 -->
            <div class="mb-6 grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="text-gray-600 dark:text-gray-400 text-sm">总域名数</div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($totalCount) }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="text-gray-600 dark:text-gray-400 text-sm">今日记录</div>
                    <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ number_format($todayCount) }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="text-gray-600 dark:text-gray-400 text-sm">筛选结果</div>
                    <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ number_format($filteredCount) }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="text-gray-600 dark:text-gray-400 text-sm">最新日期</div>
                    <div class="text-lg font-bold text-purple-600 dark:text-purple-400">{{ $latestDate ?? '暂无数据' }}</div>
                </div>
            </div>

            <!-- 筛选和排序表单 -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <form method="GET" action="{{ route('ranking-changes.index') }}" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <!-- 日期筛选 -->
                            <div>
                                <label for="date_filter" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">日期筛选</label>
                                <select name="date_filter" id="date_filter" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                    <option value="">全部日期</option>
                                    <option value="today" {{ $dateFilter === 'today' ? 'selected' : '' }}>今天</option>
                                    <option value="yesterday" {{ $dateFilter === 'yesterday' ? 'selected' : '' }}>昨天</option>
                                    <option value="last_7_days" {{ $dateFilter === 'last_7_days' ? 'selected' : '' }}>最近7天</option>
                                    <option value="last_30_days" {{ $dateFilter === 'last_30_days' ? 'selected' : '' }}>最近30天</option>
                                    <option value="this_month" {{ $dateFilter === 'this_month' ? 'selected' : '' }}>本月</option>
                                    <option value="last_month" {{ $dateFilter === 'last_month' ? 'selected' : '' }}>上月</option>
                                </select>
                            </div>

                            <!-- 排序字段 -->
                            <div>
                                <label for="sort" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">排序字段</label>
                                <select name="sort" id="sort" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                    <option value="record_date" {{ $sortBy === 'record_date' ? 'selected' : '' }}>记录日期</option>
                                    <option value="domain" {{ $sortBy === 'domain' ? 'selected' : '' }}>域名</option>
                                    <option value="current_ranking" {{ $sortBy === 'current_ranking' ? 'selected' : '' }}>当前排名</option>
                                    <option value="daily_change" {{ $sortBy === 'daily_change' ? 'selected' : '' }}>日变化</option>
                                    <option value="week_change" {{ $sortBy === 'week_change' ? 'selected' : '' }}>周变化</option>
                                    <option value="biweek_change" {{ $sortBy === 'biweek_change' ? 'selected' : '' }}>双周变化</option>
                                    <option value="triweek_change" {{ $sortBy === 'triweek_change' ? 'selected' : '' }}>三周变化</option>
                                    <option value="month_change" {{ $sortBy === 'month_change' ? 'selected' : '' }}>月变化</option>
                                    <option value="quarter_change" {{ $sortBy === 'quarter_change' ? 'selected' : '' }}>季度变化</option>
                                    <option value="year_change" {{ $sortBy === 'year_change' ? 'selected' : '' }}>年变化</option>
                                </select>
                            </div>

                            <!-- 排序顺序 -->
                            <div>
                                <label for="order" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">排序顺序</label>
                                <select name="order" id="order" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                    <option value="desc" {{ $sortOrder === 'desc' ? 'selected' : '' }}>降序</option>
                                    <option value="asc" {{ $sortOrder === 'asc' ? 'selected' : '' }}>升序</option>
                                </select>
                            </div>

                            <!-- 筛选字段 -->
                            <div>
                                <label for="filter_field" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">筛选字段</label>
                                <select name="filter_field" id="filter_field" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                    <option value="">无筛选</option>
                                    <option value="current_ranking" {{ $filterField === 'current_ranking' ? 'selected' : '' }}>当前排名</option>
                                    <option value="daily_change" {{ $filterField === 'daily_change' ? 'selected' : '' }}>日变化</option>
                                    <option value="week_change" {{ $filterField === 'week_change' ? 'selected' : '' }}>周变化</option>
                                    <option value="biweek_change" {{ $filterField === 'biweek_change' ? 'selected' : '' }}>双周变化</option>
                                    <option value="triweek_change" {{ $filterField === 'triweek_change' ? 'selected' : '' }}>三周变化</option>
                                    <option value="month_change" {{ $filterField === 'month_change' ? 'selected' : '' }}>月变化</option>
                                    <option value="quarter_change" {{ $filterField === 'quarter_change' ? 'selected' : '' }}>季度变化</option>
                                    <option value="year_change" {{ $filterField === 'year_change' ? 'selected' : '' }}>年变化</option>
                                </select>
                            </div>

                            <!-- 筛选操作符 -->
                            <div>
                                <label for="filter_operator" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">操作符</label>
                                <select name="filter_operator" id="filter_operator" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                    <option value=">=" {{ $filterOperator === '>=' ? 'selected' : '' }}>大于等于</option>
                                    <option value=">" {{ $filterOperator === '>' ? 'selected' : '' }}>大于</option>
                                    <option value="<=" {{ $filterOperator === '<=' ? 'selected' : '' }}>小于等于</option>
                                    <option value="<" {{ $filterOperator === '<' ? 'selected' : '' }}>小于</option>
                                    <option value="=" {{ $filterOperator === '=' ? 'selected' : '' }}>等于</option>
                                    <option value="!=" {{ $filterOperator === '!=' ? 'selected' : '' }}>不等于</option>
                                </select>
                            </div>

                            <!-- 筛选值 -->
                            <div>
                                <label for="filter_value" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">筛选值</label>
                                <input type="number" name="filter_value" id="filter_value" value="{{ $filterValue }}" 
                                       class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                       placeholder="输入数值">
                            </div>

                            <!-- 提交按钮 -->
                            <div class="flex items-end">
                                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition duration-200">
                                    应用筛选
                                </button>
                            </div>
                        </div>

                        <!-- 重置按钮 -->
                        <div class="flex justify-end">
                            <a href="{{ route('ranking-changes.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded transition duration-200">
                                重置筛选
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- 数据表格 -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">域名</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">记录日期</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">当前排名</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">日变化</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">周变化</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">双周变化</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">三周变化</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">月变化</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">季度变化</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">年变化</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($rankingChanges as $change)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $change->domain }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                        {{ $change->record_date->format('Y-m-d') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                        {{ number_format($change->current_ranking) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @if($change->daily_change !== null)
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                                @if($change->daily_trend === 'up') bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100
                                                @elseif($change->daily_trend === 'down') bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100
                                                @else bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-100
                                                @endif">
                                                @if($change->daily_trend === 'up') ↑
                                                @elseif($change->daily_trend === 'down') ↓
                                                @else →
                                                @endif
                                                {{ number_format($change->daily_change) }}
                                            </span>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @if($change->week_change !== null)
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                                @if($change->week_trend === 'up') bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100
                                                @elseif($change->week_trend === 'down') bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100
                                                @else bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-100
                                                @endif">
                                                @if($change->week_trend === 'up') ↑
                                                @elseif($change->week_trend === 'down') ↓
                                                @else →
                                                @endif
                                                {{ number_format($change->week_change) }}
                                            </span>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @if($change->biweek_change !== null)
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                                @if($change->biweek_trend === 'up') bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100
                                                @elseif($change->biweek_trend === 'down') bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100
                                                @else bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-100
                                                @endif">
                                                @if($change->biweek_trend === 'up') ↑
                                                @elseif($change->biweek_trend === 'down') ↓
                                                @else →
                                                @endif
                                                {{ number_format($change->biweek_change) }}
                                            </span>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @if($change->triweek_change !== null)
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                                @if($change->triweek_trend === 'up') bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100
                                                @elseif($change->triweek_trend === 'down') bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100
                                                @else bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-100
                                                @endif">
                                                @if($change->triweek_trend === 'up') ↑
                                                @elseif($change->triweek_trend === 'down') ↓
                                                @else →
                                                @endif
                                                {{ number_format($change->triweek_change) }}
                                            </span>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @if($change->month_change !== null)
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                                @if($change->month_trend === 'up') bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100
                                                @elseif($change->month_trend === 'down') bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100
                                                @else bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-100
                                                @endif">
                                                @if($change->month_trend === 'up') ↑
                                                @elseif($change->month_trend === 'down') ↓
                                                @else →
                                                @endif
                                                {{ number_format($change->month_change) }}
                                            </span>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @if($change->quarter_change !== null)
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                                @if($change->quarter_trend === 'up') bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100
                                                @elseif($change->quarter_trend === 'down') bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100
                                                @else bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-100
                                                @endif">
                                                @if($change->quarter_trend === 'up') ↑
                                                @elseif($change->quarter_trend === 'down') ↓
                                                @else →
                                                @endif
                                                {{ number_format($change->quarter_change) }}
                                            </span>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @if($change->year_change !== null)
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                                @if($change->year_trend === 'up') bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100
                                                @elseif($change->year_trend === 'down') bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100
                                                @else bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-100
                                                @endif">
                                                @if($change->year_trend === 'up') ↑
                                                @elseif($change->year_trend === 'down') ↓
                                                @else →
                                                @endif
                                                {{ number_format($change->year_change) }}
                                            </span>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                        暂无数据
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- 分页链接 -->
                @if($rankingChanges->hasPages())
                    <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700 border-t border-gray-200 dark:border-gray-600">
                        {{ $rankingChanges->links() }}
                    </div>
                @endif
            </div>

            <!-- 页面底部信息 -->
            <div class="mt-6 text-center text-sm text-gray-500 dark:text-gray-400">
                显示 {{ $rankingChanges->firstItem() ?? 0 }} - {{ $rankingChanges->lastItem() ?? 0 }} 
                共 {{ $rankingChanges->total() }} 条记录 
                (第 {{ $rankingChanges->currentPage() }} 页，共 {{ $rankingChanges->lastPage() }} 页)
            </div>
        </div>
    </div>

    <script>
        // 自动提交表单当选择改变时（可选功能）
        document.addEventListener('DOMContentLoaded', function() {
            const autoSubmitFields = ['date_filter'];
            
            autoSubmitFields.forEach(fieldName => {
                const field = document.getElementById(fieldName);
                if (field) {
                    field.addEventListener('change', function() {
                        // 可以选择是否自动提交，这里注释掉了
                        // this.form.submit();
                    });
                }
            });
        });
    </script>
</x-app-layout>