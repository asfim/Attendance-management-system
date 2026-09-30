@extends('layouts.app')

@section('title', 'Payroll Settings')

@section('content')
<div class="info-card p-4">
    <h5 class="fw-bold mb-4"><i class="fa-solid fa-gear me-2 text-primary"></i>Payroll & Attendance Settings</h5>

    @if(session('success'))
    <div class="alert alert-success border-0 rounded-3">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('admin.payroll.settings.save') }}">
        @csrf
        <div class="row g-4">
            {{-- Working Days --}}
            <div class="col-md-4">
                <label class="form-label fw-semibold">Working Days Per Month</label>
                <input type="number" name="working_days" class="form-control"
                    value="{{ $settings->working_days ?? 26 }}" min="1" max="31" required>
                <div class="form-text">Used to calculate per-day salary.</div>
            </div>

            {{-- Office Hours --}}
            <div class="col-md-4">
                <label class="form-label fw-semibold">Office Start Time</label>
                <input type="time" name="office_start_time" class="form-control"
                    value="{{ $settings->office_start_time ?? '09:00' }}">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Office End Time</label>
                <input type="time" name="office_end_time" class="form-control"
                    value="{{ $settings->office_end_time ?? '17:00' }}">
            </div>

            {{-- Grace Time --}}
            <div class="col-md-4">
                <label class="form-label fw-semibold">Grace Time (minutes)</label>
                <input type="number" name="grace_time_minutes" class="form-control"
                    value="{{ $settings->grace_time_minutes ?? 15 }}" min="0" max="60">
                <div class="form-text">Minutes after start time before marking as Late.</div>
            </div>

            {{-- Late Deduction --}}
            <div class="col-md-4">
                <label class="form-label fw-semibold">Late Deduction Per Occurrence (৳)</label>
                <input type="number" name="late_deduction_per_day" class="form-control" step="0.01"
                    value="{{ $settings->late_deduction_per_day ?? 0 }}" min="0">
                <div class="form-text">Set to 0 to use no fixed deduction (custom per attendance entry).</div>
            </div>

            {{-- Half Day Rate --}}
            <div class="col-md-4">
                <label class="form-label fw-semibold">Half Day Deduction Rate (%)</label>
                <input type="number" name="half_day_deduction_rate" class="form-control" step="0.01"
                    value="{{ $settings->half_day_deduction_rate ?? 50 }}" min="0" max="100">
                <div class="form-text">Percentage of daily salary deducted for a half day.</div>
            </div>

            {{-- Toggles --}}
            <div class="col-md-4">
                <label class="form-label fw-semibold d-block">Absent Deduction</label>
                <div class="form-check form-switch mt-2">
                    <input class="form-check-input" type="checkbox" name="absent_deduction_enabled" value="1"
                        id="absentToggle" {{ ($settings->absent_deduction_enabled ?? true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="absentToggle">Deduct salary for absent days</label>
                </div>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold d-block">Half Day Deduction</label>
                <div class="form-check form-switch mt-2">
                    <input class="form-check-input" type="checkbox" name="half_day_deduction_enabled" value="1"
                        id="halfDayToggle" {{ ($settings->half_day_deduction_enabled ?? true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="halfDayToggle">Deduct salary for half days</label>
                </div>
            </div>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-primary px-4">
                <i class="fa-solid fa-floppy-disk me-2"></i>Save Settings
            </button>
            <a href="{{ route('admin.payroll.index') }}" class="btn btn-outline-secondary ms-2">
                Back to Payroll
            </a>
        </div>
    </form>
</div>
@endsection
