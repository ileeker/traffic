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
            <!-- 切换按钮 -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-center space-x-4">
                        @if($domainRecord)
                        <button id="trancoBtn" 
                                class="flex items-center px-6 py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors duration-200 shadow-md">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                            Tranco 排名数据
                        </button>
                        @endif
                        
                        @if($similarwebRecord)
                        <button id="similarwebBtn" 
                                class="flex items-center px-6 py-3 bg-gray-500 text-white rounded-lg font-medium hover:bg-gray-600 transition-colors duration-200 shadow-md">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                            Similarweb 流量数据
                        </button>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Tranco 数据部分 -->
            <div id="trancoData" class="data-section">
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
            </div>

            <!-- Similarweb 数据部分 -->
            <div id="similarwebData" class="data-section" style="display: none;">
                @if($similarwebRecord)
                @php
                    $trafficData = $similarwebRecord->traffic_data['data'] ?? [];
                    $topCountries = $similarwebRecord->top_country_shares ?? [];
                @endphp

                <!-- Similarweb 基本信息 -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">网站信息</h3>
                        
                        <div class="flex flex-wrap justify-between items-center space-y-4 md:space-y-0">
                            <div class="flex items-center space-x-6">
                                <!-- 全球排名 -->
                                <div class="flex items-center">
                                    <div class="p-2 bg-green-500 bg-opacity-10 rounded-full mr-3">
                                        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-gray-600 dark:text-gray-400">全球排名</p>
                                        <p class="text-lg font-bold text-gray-900 dark:text-white">
                                            #{{ number_format($similarwebRecord->global_rank) }}
                                        </p>
                                    </div>
                                </div>
                                
                                <!-- 分类排名 -->
                                <div class="flex items-center">
                                    <div class="p-2 bg-purple-500 bg-opacity-10 rounded-full mr-3">
                                        <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-gray-600 dark:text-gray-400">分类排名</p>
                                        <p class="text-lg font-bold text-gray-900 dark:text-white">
                                            #{{ number_format($similarwebRecord->category_rank) }}
                                        </p>
                                    </div>
                                </div>

                                <!-- 月访问量 -->
                                <div class="flex items-center">
                                    <div class="p-2 bg-blue-500 bg-opacity-10 rounded-full mr-3">
                                        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-gray-600 dark:text-gray-400">月访问量</p>
                                        <p class="text-lg font-bold text-gray-900 dark:text-white">
                                            {{ number_format($similarwebRecord->current_emv) }}
                                        </p>
                                    </div>
                                </div>

                                <!-- 跳出率 -->
                                <div class="flex items-center">
                                    <div class="p-2 bg-red-500 bg-opacity-10 rounded-full mr-3">
                                        <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-gray-600 dark:text-gray-400">跳出率</p>
                                        <p class="text-lg font-bold text-gray-900 dark:text-white">
                                            {{ number_format($similarwebRecord->current_bounce_rate * 100, 1) }}%
                                        </p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- 网站标题 -->
                            @if($similarwebRecord->title)
                            <div class="text-right">
                                <p class="text-xs text-gray-600 dark:text-gray-400">网站标题</p>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $similarwebRecord->title }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- 流量来源 -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">流量来源</h3>
                        
                        <div class="flex flex-wrap justify-between items-center space-y-4 md:space-y-0">
                            <div class="flex items-center space-x-8">
                                <!-- 直接访问 -->
                                <div class="flex items-center">
                                    <div class="p-2 bg-blue-500 bg-opacity-10 rounded-full mr-3">
                                        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2 2z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 21l8-8-8-8"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-gray-600 dark:text-gray-400">直接访问</p>
                                        <p class="text-lg font-bold text-blue-600">{{ number_format($similarwebRecord->ts_direct * 100, 1) }}%</p>
                                    </div>
                                </div>

                                <!-- 搜索引擎 -->
                                <div class="flex items-center">
                                    <div class="p-2 bg-green-500 bg-opacity-10 rounded-full mr-3">
                                        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-gray-600 dark:text-gray-400">搜索引擎</p>
                                        <p class="text-lg font-bold text-green-600">{{ number_format($similarwebRecord->ts_search * 100, 1) }}%</p>
                                    </div>
                                </div>

                                <!-- 推荐链接 -->
                                <div class="flex items-center">
                                    <div class="p-2 bg-purple-500 bg-opacity-10 rounded-full mr-3">
                                        <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-gray-600 dark:text-gray-400">推荐链接</p>
                                        <p class="text-lg font-bold text-purple-600">{{ number_format($similarwebRecord->ts_referrals * 100, 1) }}%</p>
                                    </div>
                                </div>

                                <!-- 社交媒体 -->
                                <div class="flex items-center">
                                    <div class="p-2 bg-pink-500 bg-opacity-10 rounded-full mr-3">
                                        <svg class="w-5 h-5 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-gray-600 dark:text-gray-400">社交媒体</p>
                                        <p class="text-lg font-bold text-pink-600">{{ number_format($similarwebRecord->ts_social * 100, 1) }}%</p>
                                    </div>
                                </div>

                                <!-- 付费推广 -->
                                <div class="flex items-center">
                                    <div class="p-2 bg-orange-500 bg-opacity-10 rounded-full mr-3">
                                        <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-gray-600 dark:text-gray-400">付费推广</p>
                                        <p class="text-lg font-bold text-orange-600">{{ number_format($similarwebRecord->ts_paid_referrals * 100, 1) }}%</p>
                                    </div>
                                </div>

                                <!-- 邮件 -->
                                <div class="flex items-center">
                                    <div class="p-2 bg-red-500 bg-opacity-10 rounded-full mr-3">
                                        <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-gray-600 dark:text-gray-400">邮件</p>
                                        <p class="text-lg font-bold text-red-600">{{ number_format($similarwebRecord->ts_mail * 100, 1) }}%</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 关键词信息 -->
                @if($similarwebRecord->top_keywords)
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">热门关键词</h3>
                        <div class="flex flex-wrap gap-3">
                            @foreach(explode(';', $similarwebRecord->top_keywords) as $keyword)
                                @php
                                    $trimmedKeyword = trim($keyword);
                                    $googleSearchUrl = 'https://www.google.com/search?q=' . urlencode($trimmedKeyword);
                                @endphp
                                <a href="{{ $googleSearchUrl }}" target="_blank" 
                                   class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 hover:bg-blue-200 dark:hover:bg-blue-800 transition-colors duration-200 hover:scale-105 transform">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                    {{ $trimmedKeyword }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                <!-- 地理分布 -->
                @if(count($topCountries) > 0)
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">地理分布 (前5名)</h3>
                        
                        <div class="flex flex-wrap justify-between items-center space-y-4 md:space-y-0">
                            <div class="flex items-center space-x-6">
                                @foreach(array_slice($topCountries, 0, 5) as $country)
                                    @php
                                        $countryNames = [
                                            'DE' => '德国',
                                            'DK' => '丹麦', 
                                            'ES' => '西班牙',
                                            'FR' => '法国',
                                            'SE' => '瑞典',
                                            'US' => '美国',
                                            'CN' => '中国',
                                            'GB' => '英国',
                                            'JP' => '日本'
                                        ];
                                        $countryName = $countryNames[$country['CountryCode']] ?? $country['CountryCode'];
                                        $flagEmojis = [
                                            'DE' => '🇩🇪',
                                            'DK' => '🇩🇰', 
                                            'ES' => '🇪🇸',
                                            'FR' => '🇫🇷',
                                            'SE' => '🇸🇪',
                                            'US' => '🇺🇸',
                                            'CN' => '🇨🇳',
                                            'GB' => '🇬🇧',
                                            'JP' => '🇯🇵'
                                        ];
                                        $flag = $flagEmojis[$country['CountryCode']] ?? '🌐';
                                        
                                        // 为不同国家设置不同颜色
                                        $colors = ['blue', 'green', 'purple', 'pink', 'orange'];
                                        $colorIndex = $loop->index % count($colors);
                                        $color = $colors[$colorIndex];
                                    @endphp
                                    <div class="flex items-center">
                                        <div class="p-2 bg-{{ $color }}-500 bg-opacity-10 rounded-full mr-3">
                                            <svg class="w-5 h-5 text-{{ $color }}-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="flex items-center mb-1">
                                                <span class="text-lg mr-1">{{ $flag }}</span>
                                                <p class="text-xs font-medium text-gray-600 dark:text-gray-400">{{ $countryName }}</p>
                                            </div>
                                            <p class="text-lg font-bold text-{{ $color }}-600">{{ number_format($country['Value'] * 100, 1) }}%</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                @endif

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
                @endif
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script>
        // 页面切换功能
        document.addEventListener('DOMContentLoaded', function() {
            const trancoBtn = document.getElementById('trancoBtn');
            const similarwebBtn = document.getElementById('similarwebBtn');
            const trancoData = document.getElementById('trancoData');
            const similarwebData = document.getElementById('similarwebData');

            // 初始状态：如果有 Tranco 数据则显示 Tranco，否则显示 Similarweb
            let currentView = 'tranco';
            @if(!$domainRecord && $similarwebRecord)
                currentView = 'similarweb';
                if (similarwebData) similarwebData.style.display = 'block';
                if (trancoData) trancoData.style.display = 'none';
                if (similarwebBtn) {
                    similarwebBtn.classList.remove('bg-gray-500', 'hover:bg-gray-600');
                    similarwebBtn.classList.add('bg-blue-600', 'hover:bg-blue-700');
                }
                if (trancoBtn) {
                    trancoBtn.classList.remove('bg-blue-600', 'hover:bg-blue-700');
                    trancoBtn.classList.add('bg-gray-500', 'hover:bg-gray-600');
                }
            @endif

            // Tranco 按钮点击事件
            if (trancoBtn) {
                trancoBtn.addEventListener('click', function() {
                    if (currentView !== 'tranco') {
                        currentView = 'tranco';
                        
                        // 切换显示
                        if (trancoData) trancoData.style.display = 'block';
                        if (similarwebData) similarwebData.style.display = 'none';
                        
                        // 切换按钮样式
                        trancoBtn.classList.remove('bg-gray-500', 'hover:bg-gray-600');
                        trancoBtn.classList.add('bg-blue-600', 'hover:bg-blue-700');
                        
                        if (similarwebBtn) {
                            similarwebBtn.classList.remove('bg-blue-600', 'hover:bg-blue-700');
                            similarwebBtn.classList.add('bg-gray-500', 'hover:bg-gray-600');
                        }
                        
                        // 重新初始化 Tranco 图表
                        setTimeout(() => initTrancoChart(), 100);
                    }
                });
            }

            // Similarweb 按钮点击事件
            if (similarwebBtn) {
                similarwebBtn.addEventListener('click', function() {
                    if (currentView !== 'similarweb') {
                        currentView = 'similarweb';
                        
                        // 切换显示
                        if (similarwebData) similarwebData.style.display = 'block';
                        if (trancoData) trancoData.style.display = 'none';
                        
                        // 切换按钮样式
                        similarwebBtn.classList.remove('bg-gray-500', 'hover:bg-gray-600');
                        similarwebBtn.classList.add('bg-blue-600', 'hover:bg-blue-700');
                        
                        if (trancoBtn) {
                            trancoBtn.classList.remove('bg-blue-600', 'hover:bg-blue-700');
                            trancoBtn.classList.add('bg-gray-500', 'hover:bg-gray-600');
                        }
                        
                        // 重新初始化 Similarweb 图表
                        setTimeout(() => initSimilarwebChart(), 100);
                    }
                });
            }

            // 初始化图表
            setTimeout(() => {
                if (currentView === 'tranco') {
                    initTrancoChart();
                } else {
                    initSimilarwebChart();
                }
            }, 100);
        });

        // 图表初始化函数
        let rankingChart = null;
        let trafficChart = null;

        function initTrancoChart() {
            if (typeof Chart === 'undefined') return;
            
            // 检测暗色模式
            const isDarkMode = document.documentElement.classList.contains('dark') || 
                              window.matchMedia('(prefers-color-scheme: dark)').matches;
            const textColor = isDarkMode ? '#e5e7eb' : '#374151';
            const gridColor = isDarkMode ? '#374151' : '#e5e7eb';

            // 清除之前的图表
            if (rankingChart) {
                rankingChart.destroy();
                rankingChart = null;
            }

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
                    
                    rankingChart = new Chart(rankingCtx, {
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
                            interaction: { intersect: false, mode: 'index' },
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
                }
            }
            @endif
        }

        function initSimilarwebChart() {
            if (typeof Chart === 'undefined') return;
            
            // 检测暗色模式
            const isDarkMode = document.documentElement.classList.contains('dark') || 
                              window.matchMedia('(prefers-color-scheme: dark)').matches;
            const textColor = isDarkMode ? '#e5e7eb' : '#374151';
            const gridColor = isDarkMode ? '#374151' : '#e5e7eb';

            // 清除之前的图表
            if (trafficChart) {
                trafficChart.destroy();
                trafficChart = null;
            }

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
                    
                    trafficChart = new Chart(trafficCtx, {
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
                            interaction: { intersect: false, mode: 'index' },
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
                }
            }
            @endif
        }
    </script>
</x-app-layout>