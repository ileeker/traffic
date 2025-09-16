{{-- 你可按需替换为Tailwind/Bootstrap，这里用原生表格，简洁清晰 --}}
@extends('layouts.app')

@section('title', 'Domain Rankings (Visible Only)')

@section('content')
<div class="container" style="max-width: 1200px;">
    <h1 style="margin: 16px 0;">Domain Rankings (Visible Only)</h1>

    <div style="margin-bottom:12px; display:flex; gap:8px; align-items:center;">
        <form method="get" action="" style="display:flex; gap:8px; align-items:center;">
            <label for="per_page">Per Page</label>
            <input id="per_page" name="per_page" type="number" min="1" max="200"
                   value="{{ request('per_page', 50) }}" style="width:90px; padding:4px;">
            <button type="submit" style="padding:6px 10px;">Apply</button>
        </form>
    </div>

    <div style="overflow:auto; border:1px solid #eee; border-radius:6px;">
        <table style="width:100%; border-collapse:collapse;">
            <thead>
                <tr style="background:#fafafa;">
                    <th style="text-align:left; padding:10px; border-bottom:1px solid #eee;">#</th>
                    <th style="text-align:left; padding:10px; border-bottom:1px solid #eee;">Domain</th>
                    <th style="text-align:left; padding:10px; border-bottom:1px solid #eee;">Rank</th>
                    <th style="text-align:left; padding:10px; border-bottom:1px solid #eee;">Category</th>
                    <th style="text-align:left; padding:10px; border-bottom:1px solid #eee;">Language</th>
                    <th style="text-align:left; padding:10px; border-bottom:1px solid #eee;">Description (ZH)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($domains as $i => $row)
                    @php
                        // metadata 已在模型 casts 为 array；若没设置，可用 json_decode((string)$row->metadata, true)
                        $category = data_get($row->metadata, 'category');
                        $language = data_get($row->metadata, 'language');
                        $descZh   = data_get($row->metadata, 'description_zh');
                    @endphp
                    <tr>
                        <td style="padding:10px; border-bottom:1px solid #f1f1f1;">
                            {{ $domains->firstItem() + $i }}
                        </td>
                        <td style="padding:10px; border-bottom:1px solid #f1f1f1;">
                            {{ $row->domain }}
                        </td>
                        <td style="padding:10px; border-bottom:1px solid #f1f1f1;">
                            {{ $row->rank }}
                        </td>
                        <td style="padding:10px; border-bottom:1px solid #f1f1f1;">
                            {{ $category ?? '—' }}
                        </td>
                        <td style="padding:10px; border-bottom:1px solid #f1f1f1;">
                            {{ $language ?? '—' }}
                        </td>
                        <td style="padding:10px; border-bottom:1px solid #f1f1f1; max-width:500px;">
                            {{ $descZh ?? '—' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="padding:16px; text-align:center; color:#777;">
                            No visible domains found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:12px;">
        {{ $domains->links() }}
    </div>

    {{-- 示例：metadata 样例（供开发核对字段） --}}
    {{-- 
    {
        "category": "Empty/Placeholder",
        "language": "English",
        "final_url": "https://amateurok.net",
        "introduction": "",
        "description_en": "The website contains minimal content. It appears to be a placeholder.",
        "description_zh": "该网站内容极少，似乎是一个占位符。",
        "last_checked_at": "2025-09-16T04:37:31.268728+00:00"
    }
    --}}
</div>
@endsection
