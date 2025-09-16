<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>域名排名信息</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body {
            background-color: #f4f6f9;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
        
        .main-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.5rem 0;
            margin-bottom: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,.1);
        }
        
        .main-header h1 {
            margin: 0;
            font-size: 1.8rem;
            font-weight: 600;
        }
        
        .content-wrapper {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 15px;
        }
        
        .card {
            border: none;
            box-shadow: 0 0 20px rgba(0,0,0,.05);
            border-radius: 10px;
            overflow: hidden;
        }
        
        .card-header {
            background-color: #fff;
            border-bottom: 2px solid #f0f0f0;
            padding: 1.25rem;
        }
        
        .card-title {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 600;
            color: #333;
        }
        
        .search-box {
            position: relative;
        }
        
        .search-box input {
            border-radius: 25px;
            padding-right: 45px;
            border: 1px solid #ddd;
            transition: all 0.3s;
        }
        
        .search-box input:focus {
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            border-color: #667eea;
        }
        
        .search-box button {
            position: absolute;
            right: 5px;
            top: 50%;
            transform: translateY(-50%);
            border-radius: 50%;
            width: 35px;
            height: 35px;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #667eea;
            border: none;
            color: white;
            transition: all 0.3s;
        }
        
        .search-box button:hover {
            background: #764ba2;
        }
        
        .table-container {
            overflow-x: auto;
        }
        
        .domain-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .domain-table thead th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            padding: 12px;
            font-weight: 600;
            color: #495057;
            white-space: nowrap;
        }
        
        .domain-table thead th a {
            color: #495057;
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .domain-table thead th a:hover {
            color: #667eea;
        }
        
        .domain-table tbody td {
            padding: 12px;
            border-bottom: 1px solid #f0f0f0;
            vertical-align: middle;
        }
        
        .domain-table tbody tr {
            transition: all 0.3s;
        }
        
        .domain-table tbody tr:hover {
            background-color: #f8f9fa;
            transform: translateX(2px);
        }
        
        .ranking-badge {
            display: inline-block;
            padding: 0.35em 0.65em;
            font-size: 0.875rem;
            font-weight: 700;
            line-height: 1;
            color: #fff;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: 0.375rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .ranking-badge.na {
            background: #6c757d;
        }
        
        .domain-link {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }
        
        .domain-link:hover {
            color: #764ba2;
        }
        
        .category-badge {
            display: inline-block;
            padding: 0.25em 0.6em;
            font-size: 0.75rem;
            font-weight: 500;
            line-height: 1.5;
            color: #fff;
            background-color: #17a2b8;
            border-radius: 20px;
        }
        
        .trend-up {
            color: #28a745;
            font-weight: bold;
            font-size: 1.1rem;
        }
        
        .trend-down {
            color: #dc3545;
            font-weight: bold;
            font-size: 1.1rem;
        }
        
        .trend-stable {
            color: #6c757d;
            font-weight: bold;
            font-size: 1.1rem;
        }
        
        .change-value {
            font-size: 0.9rem;
            margin-left: 5px;
        }
        
        .change-value.positive {
            color: #28a745;
        }
        
        .change-value.negative {
            color: #dc3545;
        }
        
        .change-value.stable {
            color: #6c757d;
        }
        
        .description-text {
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            display: inline-block;
            vertical-align: middle;
        }
        
        .view-btn {
            padding: 0.25rem 0.75rem;
            font-size: 0.875rem;
            border-radius: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            text-decoration: none;
            display: inline-block;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .view-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
            color: white;
        }
        
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
        }
        
        .empty-state i {
            font-size: 4rem;
            color: #dee2e6;
            margin-bottom: 1rem;
        }
        
        .empty-state p {
            color: #6c757d;
            font-size: 1.1rem;
        }
        
        .pagination-wrapper {
            padding: 1.5rem;
            background-color: #fff;
            border-top: 2px solid #f0f0f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .pagination-info {
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .pagination {
            margin: 0;
        }
        
        .pagination .page-link {
            border: none;
            color: #667eea;
            margin: 0 2px;
            border-radius: 5px;
            transition: all 0.3s;
        }
        
        .pagination .page-link:hover {
            background-color: #667eea;
            color: white;
        }
        
        .pagination .page-item.active .page-link {
            background-color: #667eea;
            color: white;
        }
        
        @media (max-width: 768px) {
            .main-header h1 {
                font-size: 1.5rem;
            }
            
            .card-header {
                flex-direction: column;
                gap: 1rem;
            }
            
            .description-text {
                max-width: 150px;
            }
        }
    </style>
</head>
<body>
    <div class="main-header">
        <div class="content-wrapper">
            <h1><i class="fas fa-globe"></i> 域名排名信息系统</h1>
        </div>
    </div>

    <div class="content-wrapper">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-3">
                <h3 class="card-title">域名排名列表</h3>
                <form method="GET" action="{{ route('domain-rankings.index') }}" class="search-box">
                    <input type="text" 
                           name="search" 
                           class="form-control" 
                           placeholder="搜索域名、类别或描述..." 
                           value="{{ $search ?? '' }}"
                           style="width: 300px;">
                    <button type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>

            <div class="card-body p-0">
                <div class="table-container">
                    <table class="domain-table">
                        <thead>
                            <tr>
                                <th>
                                    <a href="{{ route('domain-rankings.index', ['sort' => 'current_ranking', 'direction' => ($sortField ?? '') === 'current_ranking' && ($sortDirection ?? 'asc') === 'asc' ? 'desc' : 'asc', 'search' => $search ?? '']) }}">
                                        当前排名
                                        @if(($sortField ?? '') === 'current_ranking')
                                            @if(($sortDirection ?? 'asc') === 'asc')
                                                <i class="fas fa-sort-up"></i>
                                            @else
                                                <i class="fas fa-sort-down"></i>
                                            @endif
                                        @else
                                            <i class="fas fa-sort" style="opacity: 0.3;"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ route('domain-rankings.index', ['sort' => 'domain', 'direction' => ($sortField ?? '') === 'domain' && ($sortDirection ?? 'asc') === 'asc' ? 'desc' : 'asc', 'search' => $search ?? '']) }}">
                                        域名
                                        @if(($sortField ?? '') === 'domain')
                                            @if(($sortDirection ?? 'asc') === 'asc')
                                                <i class="fas fa-sort-up"></i>
                                            @else
                                                <i class="fas fa-sort-down"></i>
                                            @endif
                                        @else
                                            <i class="fas fa-sort" style="opacity: 0.3;"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>类别</th>
                                <th>语言</th>
                                <th>中文描述</th>
                                <th class="text-center">日变化</th>
                                <th class="text-center">周变化</th>
                                <th class="text-center">两周变化</th>
                                <th class="text-center">三周变化</th>
                                <th>
                                    <a href="{{ route('domain-rankings.index', ['sort' => 'registered_at', 'direction' => ($sortField ?? '') === 'registered_at' && ($sortDirection ?? 'asc') === 'asc' ? 'desc' : 'asc', 'search' => $search ?? '']) }}">
                                        注册时间
                                        @if(($sortField ?? '') === 'registered_at')
                                            @if(($sortDirection ?? 'asc') === 'asc')
                                                <i class="fas fa-sort-up"></i>
                                            @else
                                                <i class="fas fa-sort-down"></i>
                                            @endif
                                        @else
                                            <i class="fas fa-sort" style="opacity: 0.3;"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rankings as $ranking)
                            <tr>
                                <td>
                                    @if($ranking->current_ranking)
                                        <span class="ranking-badge">{{ number_format($ranking->current_ranking) }}</span>
                                    @else
                                        <span class="ranking-badge na">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="https://{{ $ranking->domain }}" target="_blank" class="domain-link">
                                        {{ $ranking->domain }}
                                        <i class="fas fa-external-link-alt fa-xs"></i>
                                    </a>
                                </td>
                                <td>
                                    @if($ranking->metadata && isset($ranking->metadata['category']))
                                        <span class="category-badge">{{ $ranking->metadata['category'] }}</span>
                                    @else
                                        <span style="color: #999;">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($ranking->metadata && isset($ranking->metadata['language']))
                                        {{ $ranking->metadata['language'] }}
                                    @else
                                        <span style="color: #999;">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($ranking->metadata && isset($ranking->metadata['description_zh']))
                                        <span class="description-text" title="{{ $ranking->metadata['description_zh'] }}">
                                            {{ $ranking->metadata['description_zh'] }}
                                        </span>
                                    @else
                                        <span style="color: #999;">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($ranking->daily_trend === 'up')
                                        <span class="trend-up">↑</span>
                                        <span class="change-value positive">+{{ abs($ranking->daily_change ?? 0) }}</span>
                                    @elseif($ranking->daily_trend === 'down')
                                        <span class="trend-down">↓</span>
                                        <span class="change-value negative">-{{ abs($ranking->daily_change ?? 0) }}</span>
                                    @elseif($ranking->daily_trend === 'stable')
                                        <span class="trend-stable">→</span>
                                        <span class="change-value stable">0</span>
                                    @else
                                        <span style="color: #999;">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($ranking->week_trend === 'up')
                                        <span class="trend-up">↑</span>
                                        <span class="change-value positive">+{{ abs($ranking->week_change ?? 0) }}</span>
                                    @elseif($ranking->week_trend === 'down')
                                        <span class="trend-down">↓</span>
                                        <span class="change-value negative">-{{ abs($ranking->week_change ?? 0) }}</span>
                                    @elseif($ranking->week_trend === 'stable')
                                        <span class="trend-stable">→</span>
                                        <span class="change-value stable">0</span>
                                    @else
                                        <span style="color: #999;">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($ranking->biweek_trend === 'up')
                                        <span class="trend-up">↑</span>
                                        <span class="change-value positive">+{{ abs($ranking->biweek_change ?? 0) }}</span>
                                    @elseif($ranking->biweek_trend === 'down')
                                        <span class="trend-down">↓</span>
                                        <span class="change-value negative">-{{ abs($ranking->biweek_change ?? 0) }}</span>
                                    @elseif($ranking->biweek_trend === 'stable')
                                        <span class="trend-stable">→</span>
                                        <span class="change-value stable">0</span>
                                    @else
                                        <span style="color: #999;">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($ranking->triweek_trend === 'up')
                                        <span class="trend-up">↑</span>
                                        <span class="change-value positive">+{{ abs($ranking->triweek_change ?? 0) }}</span>
                                    @elseif($ranking->triweek_trend === 'down')
                                        <span class="trend-down">↓</span>
                                        <span class="change-value negative">-{{ abs($ranking->triweek_change ?? 0) }}</span>
                                    @elseif($ranking->triweek_trend === 'stable')
                                        <span class="trend-stable">→</span>
                                        <span class="change-value stable">0</span>
                                    @else
                                        <span style="color: #999;">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($ranking->registered_at)
                                        <small style="color: #666;">{{ $ranking->registered_at->format('Y-m-d') }}</small>
                                    @else
                                        <span style="color: #999;">-</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('domain-rankings.show', $ranking->id) }}" class="view-btn">
                                        <i class="fas fa-eye"></i> 查看
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="11">
                                    <div class="empty-state">
                                        <i class="fas fa-inbox"></i>
                                        <p>暂无数据</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($rankings->hasPages())
                <div class="pagination-wrapper">
                    <div class="pagination-info">
                        显示 {{ $rankings->firstItem() }} 到 {{ $rankings->lastItem() }} 共 {{ $rankings->total() }} 条记录
                    </div>
                    <div>
                        {{ $rankings->links('pagination::bootstrap-4') }}
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>