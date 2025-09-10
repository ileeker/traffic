@extends('layouts.list', ['paginator' => $rankingChanges])

@section('list_header')
<div class="flex justify-between items-center">
    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        今日排名变化数据 - {{ today()->format('Y-m-d') }}
    </h2>
    <div class="text-sm text-gray-600 dark:text-gray-400">
        今日记录 {{ number_format($todayCount) }} 条
    </div>
</div>
@endsection

@section('list_controls')
<div class="flex flex-wrap justify-between items-center space-y-4 md:space-y-0">
    <div class="flex items-center space-x-6">
        <div class="flex items-center">
            <div class="p-2 bg-blue-500 bg-opacity-10 rounded-full mr-3">
                <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-600 dark:text-gray-400">当前页面</p>
                <p class="text-lg font-bold text-gray-900 dark:text-white">
                    第 {{ $rankingChanges->currentPage() }} 页 / 共 {{ $rankingChanges->lastPage() }} 页
                </p>
            </div>
        </div>

        <div class="flex items-center">
            <div class="p-2 bg-green-500 bg-opacity-10 rounded-full mr-3">
                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-600 dark:text-gray-400">显示范围</p>
                <p class="text-lg font-bold text-gray-900 dark:text-white">
                    {{ $rankingChanges->firstItem() ?? 0 }} - {{ $rankingChanges->lastItem() ?? 0 }}
                </p>
            </div>
        </div>
    </div>

    <div class="flex items-center space-x-4">
        <div class="flex items-center space-x-2">
            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">跳转：</label>
            <input type="number" 
                   id="pageJumpInput"
                   placeholder="页码"
                   value="{{ $rankingChanges->currentPage() }}"
                   min="1"
                   max="{{ $rankingChanges->lastPage() }}"
                   class="w-16 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
            <button id="pageJumpBtn" 
                    class="px-3 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition-colors duration-200 text-sm">
                GO
            </button>
        </div>
        
        <div class="flex items-center space-x-2">
            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">过滤：</label>
            <select id="filterField" 
                    class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                <option value="">无过滤</option>
                <option value="current_ranking" {{ $filterField == 'current_ranking' ? 'selected' : '' }}>排名 ≤</option>
                <option value="daily_change" {{ $filterField == 'daily_change' ? 'selected' : '' }}>日变化 ≥</option>
                <option value="week_change" {{ $filterField == 'week_change' ? 'selected' : '' }}>周变化 ≥</option>
                <option value="biweek_change" {{ $filterField == 'biweek_change' ? 'selected' : '' }}>双周变化 ≥</option>
                <option value="triweek_change" {{ $filterField == 'triweek_change' ? 'selected' : '' }}>三周变化 ≥</option>
                <option value="month_change" {{ $filterField == 'month_change' ? 'selected' : '' }}>月变化 ≥</option>
                <option value="quarter_change" {{ $filterField == 'quarter_change' ? 'selected' : '' }}>季度变化 ≥</option>
                </select>
            <input type="number" 
                   id="filterValue"
                   placeholder="数值"
                   value="{{ $filterValue }}"
                   class="w-20 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
            <button id="applyFilter" 
                    class="px-3 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors duration-200 text-sm">
                应用
            </button>
            @if($filterField)
            <button id="clearFilter" 
                    class="px-3 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-colors duration-200 text-sm">
                清除
            </button>
            @endif
        </div>

        <div class="flex items-center space-x-2">
            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">排序：</label>
            <select id="sortSelect" 
                    class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                <option value="current_ranking-asc" {{ $sortBy == 'current_ranking' && $sortOrder == 'asc' ? 'selected' : '' }}>排名 (1→100)</option>
                <option value="current_ranking-desc" {{ $sortBy == 'current_ranking' && $sortOrder == 'desc' ? 'selected' : '' }}>排名 (100→1)</option>
                <option value="domain-asc" {{ $sortBy == 'domain' && $sortOrder == 'asc' ? 'selected' : '' }}>域名 (A→Z)</option>
                <option value="domain-desc" {{ $sortBy == 'domain' && $sortOrder == 'desc' ? 'selected' : '' }}>域名 (Z→A)</option>
                
                <option value="registered_at-asc" {{ $sortBy == 'registered_at' && $sortOrder == 'asc' ? 'selected' : '' }}>注册时间 (早→晚)</option>
                <option value="registered_at-desc" {{ $sortBy == 'registered_at' && $sortOrder == 'desc' ? 'selected' : '' }}>注册时间 (晚→早)</option>    
                
                <option value="daily_change-desc" {{ $sortBy == 'daily_change' && $sortOrder == 'desc' ? 'selected' : '' }}>日上升最多</option>
                <option value="daily_change-asc" {{ $sortBy == 'daily_change' && $sortOrder == 'asc' ? 'selected' : '' }}>日下降最多</option>
                <option value="week_change-desc" {{ $sortBy == 'week_change' && $sortOrder == 'desc' ? 'selected' : '' }}>周上升最多</option>
                <option value="week_change-asc" {{ $sortBy == 'week_change' && $sortOrder == 'asc' ? 'selected' : '' }}>周下降最多</option>
                <option value="biweek_change-desc" {{ $sortBy == 'biweek_change' && $sortOrder == 'desc' ? 'selected' : '' }}>双周上升最多</option>
                <option value="biweek_change-asc" {{ $sortBy == 'biweek_change' && $sortOrder == 'asc' ? 'selected' : '' }}>双周下降最多</option>
                <option value="triweek_change-desc" {{ $sortBy == 'triweek_change' && $sortOrder == 'desc' ? 'selected' : '' }}>三周上升最多</option>
                <option value="triweek_change-asc" {{ $sortBy == 'triweek_change' && $sortOrder == 'asc' ? 'selected' : '' }}>三周下降最多</option>
                <option value="month_change-desc" {{ $sortBy == 'month_change' && $sortOrder == 'desc' ? 'selected' : '' }}>月上升最多</option>
                <option value="month_change-asc" {{ $sortBy == 'month_change' && $sortOrder == 'asc' ? 'selected' : '' }}>月下降最多</option>
                <option value="quarter_change-desc" {{ $sortBy == 'quarter_change' && $sortOrder == 'desc' ? 'selected' : '' }}>季度上升最多</option>
                <option value="quarter_change-asc" {{ $sortBy == 'quarter_change' && $sortOrder == 'asc' ? 'selected' : '' }}>季度下降最多</option>
                </select>
        </div>
    </div>
</div>
@endsection

@section('table_head')
<tr>
    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
        域名
    </th>
    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
        当前排名
    </th>
    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
        日变化
    </th>
    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
        周变化
    </th>
    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
        双周变化
    </th>
    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
        三周变化
    </th>
    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
        月变化
    </th>
    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
        季度变化
    </th>
    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
        注册时间
    </th>
</tr>
@endsection

@section('table_body')
@forelse($rankingChanges as $change)
<tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
    <td class="px-6 py-4 whitespace-nowrap">
        <div class="flex items-center">
            <img src="https://www.google.com/s2/favicons?domain={{ $change->domain }}" 
                 alt="{{ $change->domain }}" 
                 class="w-4 h-4 mr-3 rounded-sm"
                 style="margin-right:2px"
                 onerror="this.style.display='none'">
            <a href="{{ route('domain.ranking', ['domain' => $change->domain]) }}" 
               class="text-sm font-medium text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 hover:underline">
                {{ $change->domain }}
            </a>
            <a href="https://{{ $change->domain }}" target="_blank" title="访问 {{ $change->domain }}">
                <span class="text-green-500 text-sm" style="margin-left:2px">🌐</span>
            </a>
            <span class="domain-test-status ml-2" data-domain="{{ $change->domain }}"></span>
        </div>
    </td>
    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
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
                {{ number_format(abs($change->daily_change)) }}
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
                {{ number_format(abs($change->week_change)) }}
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
                {{ number_format(abs($change->biweek_change)) }}
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
                {{ number_format(abs($change->triweek_change)) }}
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
                {{ number_format(abs($change->month_change)) }}
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
                {{ number_format(abs($change->quarter_change)) }}
            </span>
        @else
            <span class="text-gray-400">-</span>
        @endif
    </td>
    <td class="px-6 py-4 whitespace-nowrap text-sm">
        @if($change->websiteIntroduction && $change->websiteIntroduction->registered_at)
            @php
                $registeredAt = \Carbon\Carbon::parse($change->websiteIntroduction->registered_at);
                $now = \Carbon\Carbon::now();
                $diffInDays = $registeredAt->diffInDays($now);
                $diffInYears = $registeredAt->diffInYears($now);
            @endphp
            
            <div class="flex flex-col">
                <span class="text-gray-900 dark:text-white">
                    {{ $registeredAt->format('Y-m-d') }}
                </span>
                <span class="text-xs text-gray-500 dark:text-gray-400">
                    @if($diffInYears >= 1)
                        {{ number_format($diffInYears, 1) }} 年
                    @elseif($diffInDays >= 30)
                        {{ round($diffInDays / 30) }} 月
                    @else
                        {{ $diffInDays }} 天
                    @endif
                </span>
            </div>
        @else
            <span class="text-gray-400">-</span>
        @endif
    </td>
</tr>
@empty
    <tr>
        <td colspan="9" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
            暂无数据
        </td>
    </tr>
@endforelse
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 过滤字段变化时更新占位符
    document.getElementById('filterField').addEventListener('change', function() {
        const filterValue = document.getElementById('filterValue');
        
        if (this.value === 'current_ranking') {
            filterValue.placeholder = '排名值';
        } else if (this.value) {
            filterValue.placeholder = '变化值';
        } else {
            filterValue.placeholder = '数值';
        }
    });
});
</script>
@endpush

