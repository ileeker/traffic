@extends('layouts.list', ['paginator' => $domains])

@section('list_header')
<div class="flex justify-between items-center">
    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        域名数据浏览 - {{ $lastMonth }}
    </h2>
    <div class="text-sm text-gray-600 dark:text-gray-400">
        总计 {{ number_format($totalCount) }} 个域名
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
                    第 {{ $paginator->currentPage() }} 页 / 共 {{ $paginator->lastPage() }} 页
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
                    {{ $paginator->firstItem() ?? 0 }} - {{ $paginator->lastItem() ?? 0 }}
                </p>
            </div>
        </div>

        @if($filterField && $filterValue !== null && $filterValue !== '')
        <div class="flex items-center">
            <div class="p-2 bg-orange-500 bg-opacity-10 rounded-full mr-3">
                <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                </svg>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-600 dark:text-gray-400">过滤结果</p>
                <p class="text-lg font-bold text-orange-600">
                    {{ number_format($filteredCount ?? 0) }} 条
                </p>
            </div>
        </div>
        @endif
    </div>

    <div class="flex items-center space-x-4">
        <div class="flex items-center space-x-2">
            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">跳转：</label>
            <input type="number" 
                   id="pageJumpInput"
                   placeholder="页码"
                   value="{{ $paginator->currentPage() }}"
                   min="1"
                   max="{{ $paginator->lastPage() }}"
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
                <option value="current_emv" {{ $filterField == 'current_emv' ? 'selected' : '' }}>访问量 ≥</option>
                <option value="ts_direct" {{ $filterField == 'ts_direct' ? 'selected' : '' }}>直接流量 ≥</option>
                <option value="ts_search" {{ $filterField == 'ts_search' ? 'selected' : '' }}>搜索流量 ≥</option>
                <option value="ts_referrals" {{ $filterField == 'ts_referrals' ? 'selected' : '' }}>推荐流量 ≥</option>
                <option value="ts_social" {{ $filterField == 'ts_social' ? 'selected' : '' }}>社交流量 ≥</option>
                <option value="ts_paid_referrals" {{ $filterField == 'ts_paid_referrals' ? 'selected' : '' }}>付费流量 ≥</option>
                <option value="ts_mail" {{ $filterField == 'ts_mail' ? 'selected' : '' }}>邮件流量 ≥</option>
                <option value="registered_at" {{ $filterField == 'registered_at' ? 'selected' : '' }}>注册日期晚于</option>
            </select>
            <input type="number" 
                   id="filterValue"
                   placeholder="数值"
                   value="{{ $filterValue }}"
                   class="w-24 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm"
                   step="0.1"
                   min="0">
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
                <option value="current_emv-desc" {{ $sortBy == 'current_emv' && $sortOrder == 'desc' ? 'selected' : '' }}>访问量 ↓</option>
                <option value="current_emv-asc" {{ $sortBy == 'current_emv' && $sortOrder == 'asc' ? 'selected' : '' }}>访问量 ↑</option>
                <option value="ts_direct-desc" {{ $sortBy == 'ts_direct' && $sortOrder == 'desc' ? 'selected' : '' }}>直接流量 ↓</option>
                <option value="ts_search-desc" {{ $sortBy == 'ts_search' && $sortOrder == 'desc' ? 'selected' : '' }}>搜索流量 ↓</option>
                <option value="ts_referrals-desc" {{ $sortBy == 'ts_referrals' && $sortOrder == 'desc' ? 'selected' : '' }}>推荐流量 ↓</option>
                <option value="ts_social-desc" {{ $sortBy == 'ts_social' && $sortOrder == 'desc' ? 'selected' : '' }}>社交流量 ↓</option>
                <option value="ts_paid_referrals-desc" {{ $sortBy == 'ts_paid_referrals' && $sortOrder == 'desc' ? 'selected' : '' }}>付费流量 ↓</option>
                <option value="ts_mail-desc" {{ $sortBy == 'ts_mail' && $sortOrder == 'desc' ? 'selected' : '' }}>邮件流量 ↓</option>
                <option value="registered_at-desc" {{ $sortBy == 'registered_at' && $sortOrder == 'desc' ? 'selected' : '' }}>注册日期 ↓</option>
                <option value="registered_at-asc" {{ $sortBy == 'registered_at' && $sortOrder == 'asc' ? 'selected' : '' }}>注册日期 ↑</option>
            </select>
        </div>
    </div>
</div>
@endsection

@section('table_head')
<tr>
    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">域名</th>
    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">月访问量</th>
    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">直接</th>
    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">搜索</th>
    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">推荐</th>
    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">社交</th>
    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">付费</th>
    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">邮件</th>
    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">注册日期</th>
</tr>
@endsection

@section('table_body')
@forelse($domains as $domain)
<tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
    <td class="px-6 py-4 whitespace-nowrap">
        <div class="flex items-center">
            <img src="https://www.google.com/s2/favicons?domain={{ $domain->domain }}" 
                 alt="{{ $domain->domain }}" 
                 class="w-4 h-4 mr-3 rounded-sm"
                 style="margin-right:2px"
                 onerror="this.style.display='none'">
            <a href="{{ route('domain.ranking', $domain->domain) }}" 
               class="text-sm font-medium text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-200">
                {{ $domain->domain }}
            </a>
            <a href="https://{{ $domain->domain }}" target="_blank" title="访问 {{ $domain->domain }}">
                <span class="text-green-500 text-sm" style="margin-left:2px">🌐</span>
            </a>
            <span class="domain-test-status ml-2" data-domain="{{ $domain->domain }}"></span>
        </div>
    </td>
    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
        @php
            $emv = $domain->current_emv;
            if ($emv >= 1000000000) {
                $formatted = number_format($emv / 1000000000, 2) . 'B';
            } elseif ($emv >= 1000000) {
                $formatted = number_format($emv / 1000000, 2) . 'M';
            } elseif ($emv >= 1000) {
                $formatted = number_format($emv / 1000, 2) . 'K';
            } else {
                $formatted = number_format($emv);
            }
        @endphp
        {{ $formatted }}
    </td>
    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-blue-600">{{ number_format($domain->ts_direct * 100, 1) }}%</td>
    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-600">{{ number_format($domain->ts_search * 100, 1) }}%</td>
    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-purple-600">{{ number_format($domain->ts_referrals * 100, 1) }}%</td>
    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-pink-600">{{ number_format($domain->ts_social * 100, 1) }}%</td>
    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-orange-600">{{ number_format($domain->ts_paid_referrals * 100, 1) }}%</td>
    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-red-600">{{ number_format($domain->ts_mail * 100, 1) }}%</td>
    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-red-600">
        @php
            $registeredAtDate = null;
            if (isset($domain->websiteIntroduction) && $domain->websiteIntroduction->registered_at) {
                $registeredAtDate = $domain->websiteIntroduction->registered_at;
            } elseif (isset($domain->registered_at) && $domain->registered_at) {
                $registeredAtDate = \Carbon\Carbon::parse($domain->registered_at);
            }
        @endphp

        @if($registeredAtDate)
            {{ $registeredAtDate->format('Y-m-d') }}
            <span class="text-xs text-gray-500 block">
                ({{ $registeredAtDate->diffForHumans() }})
            </span>
        @else
            <span class="text-gray-400">-</span>
        @endif
    </td>
</tr>
@empty
<tr>
    <td colspan="9" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
        暂无数据
    </td>
</tr>
@endforelse
@endsection

@push('scripts')
<script>
    // This script is specific to the browse page for updating the filter input type
    document.addEventListener('DOMContentLoaded', function() {
        const filterFieldSelect = document.getElementById('filterField');
        const filterValueInput = document.getElementById('filterValue');

        function updateFilterInput() {
            const isTraffic = ['ts_direct', 'ts_search', 'ts_referrals', 'ts_social', 'ts_paid_referrals', 'ts_mail'].includes(filterFieldSelect.value);
            
            filterValueInput.removeAttribute('max');
            filterValueInput.removeAttribute('step');
            
            if (filterFieldSelect.value === 'registered_at') {
                filterValueInput.type = 'date';
                filterValueInput.placeholder = '';
            } else {
                filterValueInput.type = 'number';
                if (isTraffic) {
                    filterValueInput.placeholder = '百分比 (如: 50)';
                    filterValueInput.setAttribute('max', '100');
                    filterValueInput.setAttribute('step', '0.1');
                } else if (filterFieldSelect.value === 'current_emv') {
                    filterValueInput.placeholder = '访问量 (如: 10000)';
                } else {
                    filterValueInput.placeholder = '数值';
                }
            }
        }

        filterFieldSelect.addEventListener('change', updateFilterInput);
        
        // Run on page load to set the correct input type
        updateFilterInput();
    });
</script>
@endpush
