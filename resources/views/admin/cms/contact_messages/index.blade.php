@extends('layouts.app')

@section('content')
<style>
    [data-bs-theme="dark"] .unread-row {
        background-color: rgba(59, 130, 246, 0.08) !important;
    }
</style>

<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm" style="border-radius:16px;">
            <div class="card-header bg-transparent border-0 pt-4 pb-0 px-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div>
                    <h5 class="mb-0 fw-bold text-primary">
                        <i class="fa-solid fa-inbox me-2"></i> Contact Form Messages
                        @if($unreadCount > 0)
                            <span class="badge bg-danger ms-2 rounded-pill fs-7">{{ $unreadCount }} Unread</span>
                        @endif
                    </h5>
                    <small class="text-muted">View and manage messages submitted by visitors on the contact page.</small>
                </div>
                <div>
                    <a href="{{ route('admin.cms.contact') }}" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-gear me-1"></i> Contact Settings</a>
                </div>
            </div>

            <div class="card-body p-4">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show"><i class="fa-solid fa-check-circle me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                @endif

                <!-- Search & Status Filter -->
                <form method="GET" action="{{ route('admin.cms.contact.messages') }}" class="row g-3 mb-4">
                    <div class="col-md-6 col-lg-4">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                            <input type="text" name="search" class="form-control" placeholder="Search by name, email or subject..." value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-4 col-lg-3">
                        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">All Statuses</option>
                            <option value="unread" {{ request('status') === 'unread' ? 'selected' : '' }}>Unread Messages Only</option>
                            <option value="read" {{ request('status') === 'read' ? 'selected' : '' }}>Read Messages</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-sm btn-primary w-100">Filter</button>
                    </div>
                </form>

                <!-- Messages Table -->
                <div class="table-responsive">
                    <table class="table table-hover align-middle border-top">
                        <thead>
                            <tr>
                                <th style="width: 50px;">Status</th>
                                <th>Sender</th>
                                <th>Email</th>
                                <th>Subject</th>
                                <th>Submitted Date</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($messages as $msg)
                                <tr class="{{ !$msg->is_read ? 'fw-bold unread-row bg-primary bg-opacity-10' : '' }}">
                                    <td>
                                        @if(!$msg->is_read)
                                            <span class="badge bg-danger rounded-pill" title="Unread">New</span>
                                        @else
                                            <span class="badge bg-secondary rounded-pill" title="Read"><i class="fa-solid fa-envelope-open"></i></span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center fw-bold" style="width:36px; height:36px; font-size: 0.85rem;">
                                                {{ strtoupper(substr($msg->first_name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <div>{{ $msg->full_name }}</div>
                                                @if($msg->phone)
                                                    <small class="text-muted fw-normal"><i class="fa-solid fa-phone me-1"></i>{{ $msg->phone }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td><a href="mailto:{{ $msg->email }}" class="text-decoration-none">{{ $msg->email }}</a></td>
                                    <td>
                                        <span class="text-truncate d-inline-block" style="max-width: 250px;">
                                            {{ $msg->subject }}
                                        </span>
                                    </td>
                                    <td class="text-muted small">
                                        {{ $msg->created_at->format('M d, Y h:i A') }}<br>
                                        <small>{{ $msg->created_at->diffForHumans() }}</small>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-inline-flex gap-1">
                                            <a href="{{ route('admin.cms.contact.messages.show', $msg->id) }}" class="btn btn-sm btn-outline-primary" title="View Details">
                                                <i class="fa-solid fa-eye me-1"></i> View
                                            </a>
                                            <form action="{{ route('admin.cms.contact.messages.destroy', $msg->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this message?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="fa-solid fa-inbox fa-3x mb-3 opacity-25"></i>
                                        <p class="mb-0">No contact messages received yet.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $messages->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
