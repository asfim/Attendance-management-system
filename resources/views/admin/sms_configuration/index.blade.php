@extends('layouts.app')

@section('title', 'SMS Gateway Configuration')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-3">
        <div>
            <h3 class="fw-bold mb-1"><i class="bi bi-chat-dots text-primary me-2"></i>Dynamic SMS Gateway</h3>
            <p class="text-muted mb-0">Configure your SMS API provider to send automated biometric punch alerts to guardians.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-body border-0 py-3">
            <h5 class="fw-bold mb-0">API Settings</h5>
        </div>
        <div class="card-body p-4">
            <form action="{{ route('admin.sms-configuration.update') }}" method="POST">
                @csrf
                
                <div class="form-check form-switch mb-4">
                    <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" {{ old('is_active', $setting->is_active ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label fw-bold ms-2" for="is_active">Enable Automatic SMS Notifications</label>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">SMS Gateway Provider <span class="text-danger">*</span></label>
                    <select class="form-select form-select-lg" name="gateway_provider" id="gateway_provider" onchange="toggleCustomUrl()">
                        <option value="bulksmsbd" {{ old('gateway_provider', $setting->gateway_provider ?? 'bulksmsbd') == 'bulksmsbd' ? 'selected' : '' }}>BulkSMSBD (bulksmsbd.com)</option>
                        <option value="greenweb" {{ old('gateway_provider', $setting->gateway_provider ?? '') == 'greenweb' ? 'selected' : '' }}>GreenWeb (greenweb.com.bd)</option>
                        <option value="bdbulksms" {{ old('gateway_provider', $setting->gateway_provider ?? '') == 'bdbulksms' ? 'selected' : '' }}>BD Bulk SMS (bdbulksms.net)</option>
                        <option value="custom" {{ old('gateway_provider', $setting->gateway_provider ?? '') == 'custom' ? 'selected' : '' }}>Custom Gateway API</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">API Key / Token <span class="text-danger">*</span></label>
                    <input type="text" class="form-control form-control-lg" name="api_key" value="{{ old('api_key', $setting->api_key ?? '') }}" placeholder="e.g. kWxqyomL5gsWOALgnAzv">
                    <div class="form-text">Use the API Token/Key provided by your selected SMS gateway.</div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">Sender ID / Mask / Number</label>
                    <input type="text" class="form-control form-control-lg" name="sender_id" value="{{ old('sender_id', $setting->sender_id ?? '') }}" placeholder="Enter Sender ID (e.g. 88017XXXXXXXX or Approved Mask)">
                    <div class="form-text">Leave empty if your gateway does not require a Sender ID/Mask.</div>
                </div>

                <div class="mb-4" id="custom_url_container" style="display: {{ old('gateway_provider', $setting->gateway_provider ?? 'bulksmsbd') == 'custom' ? 'block' : 'none' }};">
                    <label class="form-label fw-semibold">Custom API URL Template <span class="text-danger">*</span></label>
                    <input type="text" class="form-control form-control-lg font-monospace" name="custom_api_url" value="{{ old('custom_api_url', $setting->custom_api_url ?? '') }}" placeholder="http://api.smsbd.net/send?apikey=YOUR_KEY&to=[number]&msg=[message]">
                    <div class="form-text mt-2">
                        <strong>Instructions:</strong> Paste the full GET URL here. Replace the destination number parameter value with <code>[number]</code> and the message parameter value with <code>[message]</code>.
                    </div>
                </div>

                <script>
                    function toggleCustomUrl() {
                        var val = document.getElementById('gateway_provider').value;
                        document.getElementById('custom_url_container').style.display = (val === 'custom') ? 'block' : 'none';
                    }
                </script>

                <hr class="my-4">

                <h5 class="fw-bold mb-3">Message Templates</h5>

                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Check-In / Entry Message</label>
                        <textarea class="form-control" name="entry_message_template" rows="4" placeholder="Dear Guardian, [name] has entered the school at [time] on [date].">{{ old('entry_message_template', $setting->entry_message_template ?? 'Dear Guardian, your child [name] has safely entered the school premises at [time] on [date]. Thank you.') }}</textarea>
                        <div class="form-text">Available Tags: <code>[name]</code>, <code>[time]</code>, <code>[date]</code></div>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Check-Out / Exit Message</label>
                        <textarea class="form-control" name="exit_message_template" rows="4" placeholder="Dear Guardian, [name] has left the school at [time] on [date].">{{ old('exit_message_template', $setting->exit_message_template ?? 'Dear Guardian, your child [name] has left the school premises at [time] on [date]. Thank you.') }}</textarea>
                        <div class="form-text">Available Tags: <code>[name]</code>, <code>[time]</code>, <code>[date]</code></div>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-top">
                    <button type="submit" class="btn btn-primary rounded-pill px-4 py-2 fw-bold">
                        <i class="bi bi-save me-1"></i> Save Configuration
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
