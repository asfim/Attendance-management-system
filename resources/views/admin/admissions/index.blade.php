@extends('layouts.app')

@section('content')
<div class="card glass-card border-0 p-4">
    <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
        <h5 class="fw-bold m-0"><i class="fa-solid fa-file-signature me-2"></i>Admission Applications</h5>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm" style="border-radius: 12px;">
            <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
        </div>
    @endif

    <div class="table-responsive">
        <table class="table table-hover align-middle custom-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Applicant Name</th>
                    <th>Target Class</th>
                    <th>Target Section</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($applications as $app)
                    <tr>
                        <td>{{ $app->created_at->format('d M, Y') }}</td>
                        <td>
                            <div class="fw-bold">{{ $app->name }}</div>
                            <small class="text-muted">{{ $app->email }}</small>
                        </td>
                        <td>{{ $app->schoolClass->name ?? 'N/A' }}</td>
                        <td>{{ $app->section->name ?? 'N/A' }}</td>
                        <td>
                            @if($app->status === 'pending')
                                <span class="badge bg-warning text-dark"><i class="fa-solid fa-clock me-1"></i>Pending</span>
                            @elseif($app->status === 'accepted')
                                <span class="badge bg-success"><i class="fa-solid fa-check me-1"></i>Accepted</span>
                            @else
                                <span class="badge bg-danger"><i class="fa-solid fa-times me-1"></i>Rejected</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.admissions.show', $app->id) }}" class="btn btn-sm btn-light border shadow-sm">
                                <i class="fa-solid fa-eye me-1"></i>View
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">
                            <i class="fa-regular fa-folder-open mb-2 fs-3 d-block text-secondary opacity-50"></i>
                            No admission applications found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $applications->links() }}
    </div>
</div>
@endsection
