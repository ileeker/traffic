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
            border-bottom: 1px solid #dee2e6;
        }
        
        .domain-cell {
            text-align: left !important;
            min-width: 300px;
            padding: 12px 8px;
        }
        
        .domain-main {
            font-weight: 600;
            font-size: 14px;
            color: #2c3e50;
            margin-bottom: 4px;
            word-break: break-all;
        }
        
        .domain-description {
            font-size: 12px;
            color: #6c757d;
            line-height: 1.4;
            word-wrap: break-word;
            word-break: break-word;
            hyphens: auto;
        }
        
        .ranking-cell {
            font-weight: 700;
            font-size: 16px;
            color: #2c3e50;
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
                            <th style="width: 120px;">
                                <a href="?sort=current_ranking&direction={{ ($sortField ?? '') === 'current_ranking' && ($sortDirection ?? 'asc') === 'asc' ? 'desc' : 'asc' }}" class="sort-link">
                                    当前排名
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
                            <th style="width: 300px;">
                                <a href="?sort=domain&direction={{ ($sortField ?? '') === 'domain' && ($sortDirection ?? 'asc') === 'asc' ? 'desc' : 'asc' }}" class="sort-link">
                                    域名 & 描述
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
                            <th style="width: 80px;">类别</th>
                            <th style="width: 80px;">语言</th>
                            <th style="width: 100px;">日变化</th>
                            <th style="width: 100px;">周变化</th>
                            <th style="width: 100px;">两周变化</th>
                            <th style="width: 100px;">三周变化</th>
                            <th style="width: 120px;">
                                <a href="?sort=registered_at&direction={{ ($sortField ?? '') === 'registered_at' && ($sortDirection ?? 'asc') === 'asc' ? 'desc' : 'asc' }}" class="sort-link">
                                    注册时间
                                    @if(($sortField ?? '') === 'registered_at')
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
                            <th style="width: 80px;">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rankings as $ranking)
                            <tr>
                                <td class="ranking-cell">
                                    @if($ranking->current_ranking)
                                        {{ number_format($ranking->current_ranking) }}
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td class="domain-cell">
                                    <div class="domain-main">
                                        {{ $ranking->domain }}
                                    </div>
                                    <div class="domain-description">
                                        @if($ranking->metadata && isset($ranking->metadata['description_zh']) && !empty($ranking->metadata['description_zh']))
                                            {{ $ranking->metadata['description_zh'] }}
                                        @else
                                            <span class="text-muted">暂无描述</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @if($ranking->metadata && isset($ranking->metadata['category']))
                                        <span class="badge bg-secondary">{{ $ranking->metadata['category'] }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($ranking->metadata && isset($ranking->metadata['language']))
                                        <span class="badge bg-info">{{ $ranking->metadata['language'] }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($ranking->daily_trend === 'up')
                                        <span class="trend-up">↑ +{{ abs($ranking->daily_change ?? 0) }}</span>
                                    @elseif($ranking->daily_trend === 'down')
                                        <span class="trend-down">↓ -{{ abs($ranking->daily_change ?? 0) }}</span>
                                    @elseif($ranking->daily_trend === 'stable')
                                        <span class="trend-stable">→ 0</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($ranking->week_trend === 'up')
                                        <span class="trend-up">↑ +{{ abs($ranking->week_change ?? 0) }}</span>
                                    @elseif($ranking->week_trend === 'down')
                                        <span class="trend-down">↓ -{{ abs($ranking->week_change ?? 0) }}</span>
                                    @elseif($ranking->week_trend === 'stable')
                                        <span class="trend-stable">→ 0</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($ranking->biweek_trend === 'up')
                                        <span class="trend-up">↑ +{{ abs($ranking->biweek_change ?? 0) }}</span>
                                    @elseif($ranking->biweek_trend === 'down')
                                        <span class="trend-down">↓ -{{ abs($ranking->biweek_change ?? 0) }}</span>
                                    @elseif($ranking->biweek_trend === 'stable')
                                        <span class="trend-stable">→ 0</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($ranking->triweek_trend === 'up')
                                        <span class="trend-up">↑ +{{ abs($ranking->triweek_change ?? 0) }}</span>
                                    @elseif($ranking->triweek_trend === 'down')
                                        <span class="trend-down">↓ -{{ abs($ranking->triweek_change ?? 0) }}</span>
                                    @elseif($ranking->triweek_trend === 'stable')
                                        <span class="trend-stable">→ 0</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($ranking->registered_at)
                                        {{ $ranking->registered_at->format('Y-m-d') }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <button class="btn-view" onclick="viewDomain('{{ $ranking->domain }}')">
                                        查看
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">
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
        function viewDomain(domain) {
            // 这里可以添加查看域名详情的逻辑
            console.log('查看域名:', domain);
            // 例如：window.open('/domain/' + domain, '_blank');
        }
    </script>
</body>
</html>