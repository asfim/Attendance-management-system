@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold"><i class="fa-solid fa-users text-primary me-2"></i>Student Leave Applications</h4>
</div>

<div class="card glass-card border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>Student</th>
                        <th>Leave Type</th>
                        <th>Duration</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($applications as $app)
                    <tr>
                        <td class="fw-medium">
                            {{ $app->applicant->user->name ?? 'Unknown' }}
                        </td>
                        <td>{{ $app->leaveType->name ?? 'N/A' }}</td>
                        <td>
                            {{ $app->start_date->format('d M') }} - {{ $app->end_date->format('d M, Y') }}
                        </td>
                        <td>
                            @if($app->status === 'approved')
                                <span class="badge bg-success">Approved</span>
                            @elseif($app->status === 'rejected')
                                <span class="badge bg-danger">Rejected</span>
                            @else
                                <span class="badge bg-warning text-dark">Pending</span>
                            @endif
                        </td>
                        <td class="text-end">
                            @if($app->file_path)
                                <a href="{{ asset('storage/' . $app->file_path) }}" target="_blank" class="btn btn-sm btn-info text-white"><i class="fa-solid fa-paperclip"></i></a>
                            @endif
                            
                            @if($app->status === 'pending')
                            <form action="{{ route('teacher.student-leaves.update', $app->id) }}" method="POST" class="d-inline-block">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="status" value="approved">
                                <button type="submit" class="btn btn-sm btn-success" title="Approve"><i class="fa-solid fa-check"></i></button>
                            </form>
                            <form action="{{ route('teacher.student-leaves.update', $app->id) }}" method="POST" class="d-inline-block">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="status" value="rejected">
                                <button type="submit" class="btn btn-sm btn-danger" title="Reject"><i class="fa-solid fa-xmark"></i></button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">No student leave applications found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3 border-top">
            {{ $applications->links() }}
        </div>
    </div>
</div>
@endsection
