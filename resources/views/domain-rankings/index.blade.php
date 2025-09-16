@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">域名排名信息</h3>
                    <div class="card-tools">
                        <form method="GET" action="{{ route('domain-rankings.index') }}" class="form-inline">
                            <div class="input-group">
                                <input type="text" 
                                       name="search" 
                                       class="form-control" 
                                       placeholder="搜索域名、类别或描述..." 
                                       value="{{ $search }}">
                                <div class="input-group-append">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="fas fa-search"></i> 搜索
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card-body table-responsive p-0">
                    <table class="table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th>
                                    <a href="{{ route('domain-rankings.index', ['sort' => 'current_ranking', 'direction' => $sortField === 'current_ranking' && $sortDirection === 'asc' ? 'desc' : 'asc', 'search' => $search]) }}">
                                        当前排名
                                        @if($sortField === 'current_ranking')
                                            @if($sortDirection === 'asc')
                                                <i class="fas fa-sort-up"></i>
                                            @else
                                                <i class="fas fa-sort-down"></i>
                                            @endif
                                        @endif
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ route('domain-rankings.index', ['sort' => 'domain', 'direction' => $sortField === 'domain' && $sortDirection === 'asc' ? 'desc' : 'asc', 'search' => $search]) }}">
                                        域名
                                        @if($sortField === 'domain')
                                            @if($sortDirection === 'asc')
                                                <i class="fas fa-sort-up"></i>
                                            @else
                                                <i class="fas fa-sort-down"></i>
                                            @endif
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
                                    <a href="{{ route('domain-rankings.index', ['sort' => 'registered_at', 'direction' => $sortField === 'registered_at' && $sortDirection === 'asc' ? 'desc' : 'asc', 'search' => $search]) }}">
                                        注册时间
                                        @if($sortField === 'registered_at')
                                            @if($sortDirection === 'asc')
                                                <i class="fas fa-sort-up"></i>
                                            @else
                                                <i class="fas fa-sort-down"></i>
                                            @endif
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
                                        <span class="badge badge-primary">{{ number_format($ranking->current_ranking) }}</span>
                                    @else
                                        <span class="badge badge-secondary">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="https://{{ $ranking->domain }}" target="_blank" class="text-primary">
                                        {{ $ranking->domain }}
                                        <i class="fas fa-external-link-alt fa-xs"></i>
                                    </a>
                                </td>
                                <td>
                                    @if($ranking->metadata && isset($ranking->metadata['category']))
                                        <span class="badge badge-info">{{ $ranking->metadata['category'] }}</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if($ranking->metadata && isset($ranking->metadata['language']))
                                        {{ $ranking->metadata['language'] }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if($ranking->metadata && isset($ranking->metadata['description_zh']))
                                        <span title="{{ $ranking->metadata['description_zh'] }}" data-toggle="tooltip">
                                            {{ Str::limit($ranking->metadata['description_zh'], 50) }}
                                        </span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-center">
                                    @php
                                        $dailyTrendIcon = \App\Http\Controllers\DomainRankingController::getTrendIcon($ranking->daily_trend);
                                        $dailyChange = \App\Http\Controllers\DomainRankingController::formatChange($ranking->daily_change, $ranking->daily_trend);
                                    @endphp
                                    {!! $dailyTrendIcon !!} {!! $dailyChange !!}
                                </td>
                                <td class="text-center">
                                    @php
                                        $weekTrendIcon = \App\Http\Controllers\DomainRankingController::getTrendIcon($ranking->week_trend);
                                        $weekChange = \App\Http\Controllers\DomainRankingController::formatChange($ranking->week_change, $ranking->week_trend);
                                    @endphp
                                    {!! $weekTrendIcon !!} {!! $weekChange !!}
                                </td>
                                <td class="text-center">
                                    @php
                                        $biweekTrendIcon = \App\Http\Controllers\DomainRankingController::getTrendIcon($ranking->biweek_trend);
                                        $biweekChange = \App\Http\Controllers\DomainRankingController::formatChange($ranking->biweek_change, $ranking->biweek_trend);
                                    @endphp
                                    {!! $biweekTrendIcon !!} {!! $biweekChange !!}
                                </td>
                                <td class="text-center">
                                    @php
                                        $triweekTrendIcon = \App\Http\Controllers\DomainRankingController::getTrendIcon($ranking->triweek_trend);
                                        $triweekChange = \App\Http\Controllers\DomainRankingController::formatChange($ranking->triweek_change, $ranking->triweek_trend);
                                    @endphp
                                    {!! $triweekTrendIcon !!} {!! $triweekChange !!}
                                </td>
                                <td>
                                    @if($ranking->registered_at)
                                        <small>{{ $ranking->registered_at->format('Y-m-d') }}</small>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('domain-rankings.show', $ranking->id) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i> 查看
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="11" class="text-center">
                                    <div class="py-4">
                                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">暂无数据</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($rankings->hasPages())
                <div class="card-footer clearfix">
                    <div class="float-right">
                        {{ $rankings->links() }}
                    </div>
                    <div class="float-left">
                        显示 {{ $rankings->firstItem() }} 到 {{ $rankings->lastItem() }} 共 {{ $rankings->total() }} 条记录
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
    .table th a {
        color: #333;
        text-decoration: none;
    }
    .table th a:hover {
        color: #007bff;
    }
    .badge-primary {
        font-size: 0.9em;
    }
    .text-success {
        color: #28a745 !important;
        font-weight: bold;
    }
    .text-danger {
        color: #dc3545 !important;
        font-weight: bold;
    }
    .text-secondary {
        color: #6c757d !important;
    }
</style>

@push('scripts')
<script>
    $(function () {
        $('[data-toggle="tooltip"]').tooltip()
    })
</script>
@endpush
@endsection