@extends('layouts.app')

@section('content')
<div class="row g-4">
    <!-- Noticeboard Config -->
    <div class="col-md-6">
        <div class="card glass-card border-0 p-4">
            @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('create_notice'))
            <h5 class="fw-bold mb-4">Publish Notice</h5>
            <form action="{{ route('admin.notices.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <input type="text" name="title" class="form-control" placeholder="Notice Title" required>
                </div>
                <div class="mb-3">
                    <textarea name="content" class="form-control" placeholder="Notice Content" rows="4" required></textarea>
                </div>
                <div class="mb-3">
                    <select name="target_audience" class="form-select" required>
                        <option value="all">All</option>
                        <option value="teachers">Teachers</option>
                        <option value="students">Students</option>
                        <option value="parents">Parents</option>
                        <option value="staff">Staff</option>
                    </select>
                </div>
                <div class="mb-3">
                    <select name="shift_id" class="form-select">
                        <option value="">All Shifts</option>
                        @foreach($shifts as $shift)
                            <option value="{{ $shift->id }}">{{ $shift->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label text-secondary">Publish Date</label>
                    <input type="datetime-local" name="published_at" class="form-control" value="{{ now()->format('Y-m-d\TH:i') }}" required>
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" name="send_sms" class="form-check-input" id="sendSmsCheckbox" value="1">
                    <label class="form-check-label text-secondary" for="sendSmsCheckbox">
                        Send SMS Notification
                    </label>
                </div>
                <button type="submit" class="btn btn-primary w-100"><i class="fa-solid fa-paper-plane me-2"></i>Publish Notice</button>
            </form>
            @endif
        </div>
    </div>

    <!-- Event Config -->
    <div class="col-md-6">
        <div class="card glass-card border-0 p-4">
            <h5 class="fw-bold mb-4">Schedule Event</h5>
            <form action="{{ route('admin.events.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <input type="text" name="title" class="form-control" placeholder="Event Title" required>
                </div>
                <div class="mb-3">
                    <textarea name="description" class="form-control" placeholder="Event Description" rows="3"></textarea>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label text-secondary">Start Date</label>
                        <input type="datetime-local" name="start_date" class="form-control" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label text-secondary">End Date</label>
                        <input type="datetime-local" name="end_date" class="form-control" required>
                    </div>
                </div>
                <div class="mb-3">
                    <input type="text" name="location" class="form-control" placeholder="Location">
                </div>
                <button type="submit" class="btn btn-success w-100">Schedule Event</button>
            </form>
        </div>
    </div>
</div>

<div class="row g-4 mt-2">
    <!-- Notices List -->
    <div class="col-md-6">
        <div class="card shadow border-0" style="border-radius: 15px;">
            <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center" style="border-radius: 15px 15px 0 0;">
                <h6 class="m-0 fw-bold text-primary"><i class="fa-solid fa-bullhorn me-2"></i>Published Notices</h6>
                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-20 fs-8">
                    Total {{ $notices->count() }}
                </span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light fs-7 text-secondary">
                            <tr>
                                <th class="ps-4" style="width: 50px;">#</th>
                                <th>Title</th>
                                <th>Audience</th>
                                <th>Shift</th>
                                <th>Published</th>
                            </tr>
                        </thead>
                        <tbody class="fs-7">
                            @forelse($notices as $index => $notice)
                                <tr>
                                    <td class="ps-4 text-muted">{{ $index + 1 }}</td>
                                    <td>
                                        <div class="fw-bold text-dark-emphasis">{{ $notice->title }}</div>
                                        <div class="text-muted fs-8 text-wrap" style="max-width: 250px;">{{ Str::limit($notice->content, 60) }}</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border fs-8">
                                            {{ ucfirst($notice->target_audience) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info bg-opacity-10 text-info border fs-8">
                                            {{ $notice->shift ? $notice->shift->name : 'All Shifts' }}
                                        </span>
                                    </td>
                                    <td class="text-muted fs-8">
                                        {{ $notice->published_at ? $notice->published_at->format('d M Y, h:i A') : 'N/A' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">
                                        No notices published yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Events List -->
    <div class="col-md-6">
        <div class="card shadow border-0" style="border-radius: 15px;">
            <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center" style="border-radius: 15px 15px 0 0;">
                <h6 class="m-0 fw-bold text-success"><i class="fa-solid fa-calendar-days me-2"></i>Scheduled Events</h6>
                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-20 fs-8">
                    Total {{ $events->count() }}
                </span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light fs-7 text-secondary">
                            <tr>
                                <th class="ps-4" style="width: 50px;">#</th>
                                <th>Event Details</th>
                                <th>Date & Time</th>
                                <th>Location</th>
                            </tr>
                        </thead>
                        <tbody class="fs-7">
                            @forelse($events as $index => $event)
                                <tr>
                                    <td class="ps-4 text-muted">{{ $index + 1 }}</td>
                                    <td>
                                        <div class="fw-bold text-dark-emphasis">{{ $event->title }}</div>
                                        <div class="text-muted fs-8 text-wrap" style="max-width: 200px;">{{ Str::limit($event->description, 60) }}</div>
                                    </td>
                                    <td class="fs-8">
                                        <div class="text-success fw-medium">{{ $event->start_date ? $event->start_date->format('d M Y, h:i A') : 'N/A' }}</div>
                                        <div class="text-muted">to {{ $event->end_date ? $event->end_date->format('d M Y, h:i A') : 'N/A' }}</div>
                                    </td>
                                    <td class="text-muted fs-8">
                                        {{ $event->location ?: 'N/A' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">
                                        No events scheduled yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
