<!-- PDF Letterhead Header (hidden on screen, shown during PDF generation) -->
<div id="pdf-header" class="d-none d-print-block">
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 0;">
        <tr>
            <td style="width: 15%; vertical-align: middle; padding: 10px;">
                @if(\App\Models\Setting::get('site_logo'))
                    <img src="{{ asset(\App\Models\Setting::get('site_logo')) }}" style="max-height: 60px; max-width: 100px;" alt="Logo">
                @else
                    <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #3b82f6, #1d4ed8); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; font-size: 24px; font-weight: 700;">
                        E
                    </div>
                @endif
            </td>
            <td style="vertical-align: middle; padding: 10px;">
                <div style="font-size: 20px; font-weight: 800; color: #1e293b; letter-spacing: -0.5px;">
                    {{ \App\Models\Setting::get('school_name', 'Enterprise School ERP') }}
                </div>
                <div style="font-size: 11px; color: #64748b; margin-top: 2px;">
                    {{ \App\Models\Setting::get('school_address', '123 Education Lane, Learning City') }}
                </div>
            </td>
            <td style="width: 35%; vertical-align: middle; text-align: right; padding: 10px; font-size: 10px; color: #475569; line-height: 1.7;">
                <div><strong>Phone:</strong> {{ \App\Models\Setting::get('school_phone', '+1 234 567 890') }}</div>
                <div><strong>Email:</strong> {{ \App\Models\Setting::get('school_email', 'admin@school.com') }}</div>
                <div><strong>Date:</strong> {{ now()->format('d M, Y — h:i A') }}</div>
            </td>
        </tr>
    </table>
    <div style="height: 3px; background: linear-gradient(to right, #3b82f6, #06b6d4, #8b5cf6); border-radius: 2px; margin-bottom: 5px;"></div>

    <!-- Report Title Section -->
    <div style="text-align: center; padding: 15px 0 10px 0;">
        <div style="font-size: 22px; font-weight: 700; color: #0f172a; letter-spacing: -0.5px;">
            {{ $title ?? 'Report' }}
        </div>
        @if(isset($subtitle))
        <div style="font-size: 12px; color: #64748b; margin-top: 4px;">
            {!! $subtitle !!}
        </div>
        @endif
    </div>
</div>
