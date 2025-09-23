@extends('layouts.list', ['paginator' => $rankings])

@section('list_header')
<div class="flex justify-between items-center">
    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        域名排名信息系统 - {{ today()->format('Y-m-d') }}
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
                    第 {{ $rankings->currentPage() }} 页 / 共 {{ $rankings->lastPage() }} 页
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
                    {{ $rankings->firstItem() ?? 0 }} - {{ $rankings->lastItem() ?? 0 }}
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
                   value="{{ $rankings->currentPage() }}"
                   min="1"
                   max="{{ $rankings->lastPage() }}"
                   class="w-16 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
            <button id="pageJumpBtn" 
                    class="px-3 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition-colors duration-200 text-sm">
                GO
            </button>
        </div>
        
        <div class="flex items-center space-x-2">
            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">分类筛选：</label>
            <select id="categoryFilter" 
                    class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                <option value="">全部分类</option>
                @foreach($categories as $category)
                    <option value="{{ htmlspecialchars($category, ENT_QUOTES, 'UTF-8') }}" 
                            {{ $selectedCategory == $category ? 'selected' : '' }}>
                        {{ $category }}
                    </option>
                @endforeach
            </select>
            @if($selectedCategory)
            <button id="clearCategoryFilter" 
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
            </select>
        </div>
    </div>
</div>
@endsection

@section('table_head')
<tr>
    <th class="px-0 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
        排名
    </th>
    <th class="px-0 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
        域名 & 描述
    </th>
    <th class="px-0 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
        分类
    </th>
    <th class="px-0 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
        语言
    </th>
    <th class="px-0 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
        日变化
    </th>
    <th class="px-0 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
        周变化
    </th>
    <th class="px-0 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
        双周变化
    </th>
    <th class="px-0 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
        三周变化
    </th>
    <th class="px-0 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
        注册时间
    </th>
</tr>
@endsection

@section('table_body')
@forelse($rankings as $ranking)
<tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
    <td class="px-0 py-4 whitespace-nowrap text-sm font-bold text-gray-900 dark:text-white">
        @if($ranking->current_ranking)
            {{ number_format($ranking->current_ranking) }}
        @else
            <span class="text-gray-400">-</span>
        @endif
    </td>
    <td class="px-0 py-4" style="min-width: 300px;">
        <div class="flex items-center mb-2">
            <img src="https://www.google.com/s2/favicons?domain={{ $ranking->domain }}" 
                 alt="{{ $ranking->domain }}" 
                 class="w-4 h-4 mr-3 rounded-sm"
                 style="margin-right:2px"
                 onerror="this.style.display='none'">
            <a href="https://{{ $ranking->domain }}" 
               target="_blank" 
               rel="noopener noreferrer"
               class="text-sm font-medium text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 hover:underline">
                {{ $ranking->domain }}
            </a>
            <span class="text-green-500 text-sm ml-1"><TrashIcon className="h-4 w-4 text-green-500 ml-1" /></span>
        </div>
        @if($ranking->metadata && isset($ranking->metadata['description_zh']) && !empty($ranking->metadata['description_zh']))
            <div class="text-xs text-gray-600 dark:text-gray-400 leading-relaxed">
                {{ $ranking->metadata['description_zh'] }}
            </div>
        @else
            <div class="text-xs text-gray-400">暂无描述</div>
        @endif
    </td>
    <td class="px-0 py-4 whitespace-nowrap text-sm">
        @if($ranking->category)
            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-800 dark:text-purple-100">
                {{ $ranking->category }}
            </span>
        @else
            <span class="text-gray-400">-</span>
        @endif
    </td>
    <td class="px-0 py-4 whitespace-nowrap text-sm">
        @if($ranking->metadata && isset($ranking->metadata['language']))
            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100">
                {{ $ranking->metadata['language'] }}
            </span>
        @else
            <span class="text-gray-400">-</span>
        @endif
    </td>
    <td class="px-0 py-4 whitespace-nowrap text-sm">
        @if($ranking->daily_change !== null)
            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                @if($ranking->daily_trend === 'up') bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100
                @elseif($ranking->daily_trend === 'down') bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100
                @else bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-100
                @endif">
                @if($ranking->daily_trend === 'up') ↗
                @elseif($ranking->daily_trend === 'down') ↘
                @else →
                @endif
                {{ number_format(abs($ranking->daily_change)) }}
            </span>
        @else
            <span class="text-gray-400">-</span>
        @endif
    </td>
    <td class="px-0 py-4 whitespace-nowrap text-sm">
        @if($ranking->week_change !== null)
            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                @if($ranking->week_trend === 'up') bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100
                @elseif($ranking->week_trend === 'down') bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100
                @else bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-100
                @endif">
                @if($ranking->week_trend === 'up') ↗
                @elseif($ranking->week_trend === 'down') ↘
                @else →
                @endif
                {{ number_format(abs($ranking->week_change)) }}
            </span>
        @else
            <span class="text-gray-400">-</span>
        @endif
    </td>
    <td class="px-0 py-4 whitespace-nowrap text-sm">
        @if($ranking->biweek_change !== null)
            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                @if($ranking->biweek_trend === 'up') bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100
                @elseif($ranking->biweek_trend === 'down') bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100
                @else bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-100
                @endif">
                @if($ranking->biweek_trend === 'up') ↗
                @elseif($ranking->biweek_trend === 'down') ↘
                @else →
                @endif
                {{ number_format(abs($ranking->biweek_change)) }}
            </span>
        @else
            <span class="text-gray-400">-</span>
        @endif
    </td>
    <td class="px-0 py-4 whitespace-nowrap text-sm">
        @if($ranking->triweek_change !== null)
            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                @if($ranking->triweek_trend === 'up') bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100
                @elseif($ranking->triweek_trend === 'down') bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100
                @else bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-100
                @endif">
                @if($ranking->triweek_trend === 'up') ↗
                @elseif($ranking->triweek_trend === 'down') ↘
                @else →
                @endif
                {{ number_format(abs($ranking->triweek_change)) }}
            </span>
        @else
            <span class="text-gray-400">-</span>
        @endif
    </td>
    <td class="px-0 py-4 whitespace-nowrap text-sm">
        @if($ranking->registered_at)
            @php
                $registeredAt = \Carbon\Carbon::parse($ranking->registered_at);
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
        <td colspan="9" class="px-0 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
            暂无数据
        </td>
    </tr>
@endforelse
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 页面跳转功能
    const pageJumpInput = document.getElementById('pageJumpInput');
    const pageJumpBtn = document.getElementById('pageJumpBtn');
    
    pageJumpBtn?.addEventListener('click', function() {
        const page = parseInt(pageJumpInput.value);
        const maxPage = {{ $rankings->lastPage() }};
        
        if (page >= 1 && page <= maxPage) {
            const url = new URL(window.location);
            url.searchParams.set('page', page);
            window.location.href = url.toString();
        } else {
            alert(`页码必须在 1 到 ${maxPage} 之间`);
        }
    });
    
    pageJumpInput?.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            pageJumpBtn.click();
        }
    });
    
    // 分类筛选功能
    const categoryFilter = document.getElementById('categoryFilter');
    categoryFilter?.addEventListener('change', function() {
        const category = this.value;
        const url = new URL(window.location);
        
        if (category) {
            // 使用 encodeURIComponent 确保特殊字符（如 /）被正确编码
            url.searchParams.set('category', encodeURIComponent(category));
        } else {
            url.searchParams.delete('category');
        }
        url.searchParams.delete('page'); // 重置页码
        window.location.href = url.toString();
    });
    
    // 清除分类筛选功能
    const clearCategoryFilter = document.getElementById('clearCategoryFilter');
    clearCategoryFilter?.addEventListener('click', function() {
        const url = new URL(window.location);
        url.searchParams.delete('category');
        url.searchParams.delete('page'); // 重置页码
        window.location.href = url.toString();
    });
    
    // 排序功能
    const sortSelect = document.getElementById('sortSelect');
    sortSelect?.addEventListener('change', function() {
        const [sortBy, sortOrder] = this.value.split('-');
        const url = new URL(window.location);
        url.searchParams.set('sort_by', sortBy);
        url.searchParams.set('sort_order', sortOrder);
        url.searchParams.delete('page'); // 重置页码
        window.location.href = url.toString();
    });
});
</script>
@endpush