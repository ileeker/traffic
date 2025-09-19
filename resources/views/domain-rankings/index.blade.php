<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>域名排名信息系统</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Microsoft YaHei', Arial, sans-serif;
            background-color: #f8f9fa;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px 0;
            margin-bottom: 30px;
            border-radius: 10px;
            text-align: center;
        }
        
        .table-container {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow-x: auto;
        }
        
        .table {
            margin-bottom: 0;
        }
        
        .table thead th {
            background-color: #495057;
            color: white;
            border: none;
            font-weight: 600;
            text-align: center;
            vertical-align: middle;
            white-space: nowrap;
        }
        
        .table tbody td {
            vertical-align: middle;
            text-align: center;
            border-bottom: none;
            padding: 8px 6px;
        }
        
        /* 两行布局样式 */
        .ranking-row {
            border-bottom: 1px solid #dee2e6;
        }
        
        .ranking-row-main {
            border-bottom: 1px solid #f8f9fa;
        }
        
        .ranking-row-desc {
            background-color: #f8f9fb;
            border-bottom: 2px solid #dee2e6;
        }
        
        .ranking-row-desc td {
            padding: 12px 16px;
            text-align: left;
            font-size: 13px;
            color: #495057;
            line-height: 1.5;
            word-wrap: break-word;
            word-break: break-word;
            hyphens: auto;
        }
        
        .ranking-cell {
            font-weight: 700;
            font-size: 16px;
            color: #2c3e50;
            min-width: 80px;
        }
        
        .domain-cell {
            text-align: left !important;
            min-width: 200px;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .domain-link {
            color: #2c3e50;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .domain-link:hover {
            color: #667eea;
            text-decoration: underline;
        }
        
        .category-cell {
            text-align: left !important;
            min-width: 100px;
        }
        
        .language-cell {
            text-align: left !important;
            min-width: 80px;
        }
        
        .change-cell {
            min-width: 60px;
            font-size: 12px;
        }
        
        .trend-up {
            color: #28a745;
            font-weight: bold;
        }
        
        .trend-down {
            color: #dc3545;
            font-weight: bold;
        }
        
        .trend-stable {
            color: #6c757d;
            font-weight: bold;
        }
        
        .btn-view {
            background-color: #667eea;
            border: none;
            color: white;
            padding: 5px 12px;
            border-radius: 5px;
            font-size: 12px;
            transition: all 0.3s;
        }
        
        .btn-view:hover {
            background-color: #5a6fd8;
            color: white;
        }
        
        .sort-link {
            color: white;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .sort-link:hover {
            color: #e9ecef;
        }
        
        .pagination {
            margin-top: 20px;
            justify-content: center;
        }
        
        .page-info {
            text-align: center;
            margin-top: 15px;
            color: #6c757d;
        }
        
        @media (max-width: 768px) {
            .table-container {
                padding: 10px;
            }
            
            .table {
                font-size: 12px;
            }
            
            .domain-cell {
                min-width: 250px;
            }
            
            .domain-main {
                font-size: 13px;
            }
            
            .domain-description {
                font-size: 11px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>域名排名信息系统</h1>
            <p class="mb-0">Domain Ranking Information System</p>
        </div>
        
        <div class="table-container">
            <h3 class="mb-4">域名排名列表</h3>
            
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th style="width: 80px;">
                                <a href="?sort=current_ranking&direction={{ ($sortField ?? '') === 'current_ranking' && ($sortDirection ?? 'asc') === 'asc' ? 'desc' : 'asc' }}" class="sort-link">
                                    排名
                                    @if(($sortField ?? '') === 'current_ranking')
                                        @if(($sortDirection ?? 'asc') === 'asc')
                                            ↑
                                        @else
                                            ↓
                                        @endif
                                    @else
                                        ↕
                                    @endif
                                </a>
                            </th>
                            <th style="width: 200px;">
                                <a href="?sort=domain&direction={{ ($sortField ?? '') === 'domain' && ($sortDirection ?? 'asc') === 'asc' ? 'desc' : 'asc' }}" class="sort-link">
                                    域名
                                    @if(($sortField ?? '') === 'domain')
                                        @if(($sortDirection ?? 'asc') === 'asc')
                                            ↑
                                        @else
                                            ↓
                                        @endif
                                    @else
                                        ↕
                                    @endif
                                </a>
                            </th>
                            <th style="width: 100px;">分类</th>
                            <th style="width: 80px;">语言</th>
                            <th style="width: 60px;">D</th>
                            <th style="width: 60px;">W</th>
                            <th style="width: 60px;">2W</th>
                            <th style="width: 60px;">3W</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rankings as $ranking)
                            {{-- 第一行：主要数据 --}}
                            <tr class="ranking-row-main">
                                <td class="ranking-cell">
                                    @php $currentRanking = $getRankingField($ranking, 'current_ranking'); @endphp
                                    @if($currentRanking !== '--')
                                        {{ number_format($currentRanking) }}
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td class="domain-cell">
                                    <a href="https://{{ $ranking->domain }}" target="_blank" rel="noopener noreferrer" class="domain-link">
                                        {{ $ranking->domain }}
                                    </a>
                                </td>
                                <td class="category-cell">
                                    @php $category = $getRankingField($ranking, 'category'); @endphp
                                    @if($category !== '--')
                                        <span class="badge bg-secondary">{{ $category }}</span>
                                    @else
                                        <span class="text-muted">--</span>
                                    @endif
                                </td>
                                <td class="language-cell">
                                    @php $language = $getRankingField($ranking, 'language'); @endphp
                                    @if($language !== '--')
                                        <span class="badge bg-info">{{ $language }}</span>
                                    @else
                                        <span class="text-muted">--</span>
                                    @endif
                                </td>
                                <td class="change-cell">
                                    @php $dailyChange = $getRankingField($ranking, 'daily_change'); @endphp
                                    {!! $formatChange($dailyChange) !!}
                                </td>
                                <td class="change-cell">
                                    @php $weeklyChange = $getRankingField($ranking, 'weekly_change'); @endphp
                                    {!! $formatChange($weeklyChange) !!}
                                </td>
                                <td class="change-cell">
                                    @php $biweeklyChange = $getRankingField($ranking, 'biweekly_change'); @endphp
                                    {!! $formatChange($biweeklyChange) !!}
                                </td>
                                <td class="change-cell">
                                    @php $threeWeeksChange = $getRankingField($ranking, 'three_weeks_change'); @endphp
                                    {!! $formatChange($threeWeeksChange) !!}
                                </td>
                            </tr>
                            {{-- 第二行：描述信息 --}}
                            <tr class="ranking-row-desc">
                                <td colspan="8">
                                    @php $description = $getRankingField($ranking, 'description_zh'); @endphp
                                    @if($description !== '')
                                        <strong>描述：</strong>{{ $description }}
                                    @else
                                        <span class="text-muted"><strong>描述：</strong>暂无描述信息</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="fas fa-info-circle"></i> 暂无数据
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($rankings->hasPages())
                <div class="page-info">
                    显示 {{ $rankings->firstItem() }} 到 {{ $rankings->lastItem() }} 共 {{ $rankings->total() }} 条记录
                </div>
                <div class="d-flex justify-content-center">
                    {{ $rankings->links('pagination::bootstrap-4') }}
                </div>
            @endif
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // 页面已经通过链接直接跳转到目标网站，无需额外的JavaScript功能
    </script>
</body>
</html>
