@extends('layouts.list', ['paginator' => $monitoredDomains])

@section('list_header')
<div class="flex justify-between items-center">
    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        监控域名数据 - {{ today()->format('Y-m-d') }}
    </h2>
    <div class="text-sm text-gray-600 dark:text-gray-400">
        总计记录 {{ number_format($totalCount) }} 条
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
                    第 {{ $monitoredDomains->currentPage() }} 页 / 共 {{ $monitoredDomains->lastPage() }} 页
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
                    {{ $monitoredDomains->firstItem() ?? 0 }} - {{ $monitoredDomains->lastItem() ?? 0 }}
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
                   value="{{ $monitoredDomains->currentPage() }}"
                   min="1"
                   max="{{ $monitoredDomains->lastPage() }}"
                   class="w-16 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
            <button id="pageJumpBtn" 
                    class="px-3 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition-colors duration-200 text-sm">
                GO
            </button>
        </div>

        <div class="flex items-center space-x-2">
            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">排序：</label>
            <select id="sortSelect" 
                    class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                <option value="domain-asc">域名 (A→Z)</option>
                <option value="domain-desc">域名 (Z→A)</option>
                <option value="ranking-asc">排名 (1→100)</option>
                <option value="ranking-desc">排名 (100→1)</option>
                <option value="emv-desc">EMV 高→低</option>
                <option value="emv-asc">EMV 低→高</option>
                <option value="registered-desc">注册时间 (新→旧)</option>
                <option value="registered-asc">注册时间 (旧→新)</option>
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
        描述
    </th>
    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
        注册时间
    </th>
    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
        当前排名
    </th>
    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
        EMV 估值
    </th>
</tr>
@endsection

@section('table_body')
@if(isset($error))
<tr>
    <td colspan="5" class="px-6 py-4 text-center text-sm text-red-500 dark:text-red-400">
        <div class="flex items-center justify-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
            </svg>
            加载数据时出错: {{ $error }}
        </div>
    </td>
</tr>
@else
@forelse($monitoredDomains as $domain)
<tr class="hover:bg-gray-50 dark:hover:bg-gray-700" data-registered="{{ $domain['registered_at'] ? $domain['registered_at']->format('Y-m-d') : '' }}">
    <td class="px-6 py-4 whitespace-nowrap">
        <div class="flex items-center">
            <img src="https://www.google.com/s2/favicons?domain={{ $domain['domain'] }}" 
                 alt="{{ $domain['domain'] }}" 
                 class="w-4 h-4 mr-3 rounded-sm"
                 style="margin-right:2px"
                 onerror="this.style.display='none'">
            <a href="{{ route('domain.ranking', ['domain' =>$domain['domain']]) }}" 
               class="text-sm font-medium text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 hover:underline">
                 {{ $domain['domain'] }}
            </a>
            <a href="https://{{ $domain['domain'] }}" target="_blank" title="访问 {{ $domain['domain'] }}">
                <span class="text-green-500 text-sm" style="margin-left:2px">🌐</span>
            </a>
        </div>
    </td>
    <td class="px-6 py-4 whitespace-nowrap">
        <div class="text-sm text-gray-900 dark:text-white max-w-xs truncate" title="{{ $domain['description'] }}">
            {{ $domain['description'] ?? '-' }}
        </div>
    </td>
    <td class="px-6 py-4 whitespace-nowrap">
        <div class="text-sm text-gray-900 dark:text-white">
            @if($domain['registered_at'])
                {{ $domain['registered_at']->format('Y-m-d') }}
            @else
                <span class="text-gray-400">-</span>
            @endif
        </div>
    </td>
    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
        @if($domain['current_ranking'])
            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100">
                #{{ number_format($domain['current_ranking']) }}
            </span>
        @else
            <span class="text-gray-400">-</span>
        @endif
    </td>
    <td class="px-6 py-4 whitespace-nowrap text-sm">
        @if($domain['current_emv'])
            <div class="text-sm font-medium text-gray-900 dark:text-white">
                @php
                $emv = $domain['current_emv'];
                if ($emv >= 1000000000) {
                    $formatted = number_format($emv / 1000000000, 1) . 'B';
                } elseif ($emv >= 1000000) {
                    $formatted = number_format($emv / 1000000, 1) . 'M';
                } elseif ($emv >= 1000) {
                    $formatted = number_format($emv / 1000, 1) . 'K';
                } else {
                    $formatted = number_format($emv);
                }
                @endphp
                {{ $formatted }}
            </div>
        @else
            <span class="text-gray-400">-</span>
        @endif
    </td>
</tr>
@empty
    <tr>
        <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
            暂无数据
        </td>
    </tr>
@endforelse
@endif
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 页面跳转功能
    const pageJumpBtn = document.getElementById('pageJumpBtn');
    const pageJumpInput = document.getElementById('pageJumpInput');
    
    pageJumpBtn.addEventListener('click', function() {
        const page = pageJumpInput.value;
        if (page && page >= 1 && page <= {{ $monitoredDomains->lastPage() }}) {
            const url = new URL(window.location);
            url.searchParams.set('page', page);
            window.location = url.toString();
        }
    });
    
    pageJumpInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            pageJumpBtn.click();
        }
    });

    // 排序功能
    const sortSelect = document.getElementById('sortSelect');
    sortSelect.addEventListener('change', function() {
        const [field, order] = this.value.split('-');
        const tbody = document.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr[data-registered]'));
        
        rows.sort((a, b) => {
            let valueA, valueB;
            
            switch(field) {
                case 'domain':
                    valueA = a.querySelector('td:first-child a').textContent;
                    valueB = b.querySelector('td:first-child a').textContent;
                    break;
                case 'ranking':
                    valueA = a.querySelector('td:nth-child(4) span') ? 
                        parseInt(a.querySelector('td:nth-child(4) span').textContent.replace(/[#,]/g, '')) : 999999;
                    valueB = b.querySelector('td:nth-child(4) span') ? 
                        parseInt(b.querySelector('td:nth-child(4) span').textContent.replace(/[#,]/g, '')) : 999999;
                    break;
                case 'emv':
                    const getEmvValue = (row) => {
                        const emvElement = row.querySelector('td:nth-child(5) div');
                        if (!emvElement) return 0;
                        
                        const text = emvElement.textContent;
                        if (text === '-') return 0;
                        
                        const value = parseFloat(text);
                        if (text.includes('B')) return value * 1000000000;
                        if (text.includes('M')) return value * 1000000;
                        if (text.includes('K')) return value * 1000;
                        return value;
                    };
                    valueA = getEmvValue(a);
                    valueB = getEmvValue(b);
                    break;
                case 'registered':
                    valueA = a.getAttribute('data-registered') || '0000-00-00';
                    valueB = b.getAttribute('data-registered') || '0000-00-00';
                    break;
                default:
                    return 0;
            }
            
            if (order === 'asc') {
                return valueA > valueB ? 1 : -1;
            } else {
                return valueA < valueB ? 1 : -1;
            }
        });
        
        rows.forEach(row => tbody.appendChild(row));
    });
});
</script>
@endpush