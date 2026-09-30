<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>{{ $notice->title }}</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 13px; color: #1a1a2e; background: #fff; }

    .header {
        background: #0A1B32;
        color: #fff;
        padding: 28px 36px;
        border-bottom: 4px solid #C9A84C;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .header .school-name { font-size: 18px; font-weight: bold; letter-spacing: 0.5px; }
    .header .school-sub { font-size: 11px; color: #B9C2D0; margin-top: 3px; }
    .header .notice-badge {
        background: #C9A84C;
        color: #0A1B32;
        padding: 5px 14px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: bold;
        letter-spacing: 1px;
        text-transform: uppercase;
    }

    .meta-bar {
        background: #f7f4ec;
        border-bottom: 1px solid #e2ddd5;
        padding: 12px 36px;
        display: flex;
        gap: 32px;
    }
    .meta-bar .meta-item { font-size: 11px; color: #666; }
    .meta-bar .meta-item strong { color: #0A1B32; display: block; font-size: 12px; }

    .body { padding: 32px 36px 24px; }

    .notice-title {
        font-size: 22px;
        font-weight: bold;
        color: #0A1B32;
        margin-bottom: 20px;
        padding-bottom: 14px;
        border-bottom: 2px solid #C9A84C;
        line-height: 1.4;
    }

    .notice-content {
        font-size: 13px;
        line-height: 1.9;
        color: #333;
        text-align: justify;
    }

    .footer {
        margin-top: 40px;
        padding: 20px 36px;
        border-top: 1px solid #e2ddd5;
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
    }
    .footer .issued-by { font-size: 11px; color: #666; }
    .footer .issued-by strong { color: #0A1B32; font-size: 13px; display: block; margin-bottom: 3px; }
    .footer .stamp {
        text-align: center;
        border: 2px dashed #C9A84C;
        padding: 10px 20px;
        border-radius: 8px;
        font-size: 10px;
        color: #C9A84C;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .watermark {
        font-size: 9px;
        color: #999;
        text-align: center;
        margin-top: 14px;
        padding: 8px 36px;
        background: #f7f4ec;
    }
</style>
</head>
<body>

    <div class="header">
        <div>
            <div class="school-name">{{ \App\Models\Setting::get('site_name', 'Meridian International School & College') }}</div>
            <div class="school-sub">Official Notice Board — {{ date('Y') }}</div>
        </div>
        <div class="notice-badge">Official Notice</div>
    </div>

    <div class="meta-bar">
        <div class="meta-item">
            <strong>Category</strong>
            {{ ucfirst($notice->target_audience ?? 'General') }}
        </div>
        <div class="meta-item">
            <strong>Published On</strong>
            {{ optional($notice->published_at ?? $notice->created_at)->format('d F Y') }}
        </div>
        @if($notice->expires_at)
        <div class="meta-item">
            <strong>Expires On</strong>
            {{ $notice->expires_at->format('d F Y') }}
        </div>
        @endif
        <div class="meta-item">
            <strong>Notice ID</strong>
            #{{ str_pad($notice->id, 4, '0', STR_PAD_LEFT) }}
        </div>
    </div>

    <div class="body">
        <div class="notice-title">{{ $notice->title }}</div>
        <div class="notice-content">
            {!! nl2br(e($notice->content)) !!}
        </div>

        <div class="footer">
            <div class="issued-by">
                <strong>Academic Office</strong>
                {{ \App\Models\Setting::get('site_name', 'Meridian International School & College') }}
            </div>
            <div class="stamp">Authorized</div>
        </div>
    </div>

    <div class="watermark">
        This is an official notice. Generated on {{ now()->format('d M Y, h:i A') }} — {{ \App\Models\Setting::get('site_name', 'Meridian International School') }}
    </div>

</body>
</html>
