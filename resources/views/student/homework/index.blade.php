@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold"><i class="fa-solid fa-book-open text-primary me-2"></i>My Homework</h4>
</div>

<div class="card glass-card border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>Title</th>
                        <th>Subject</th>
                        <th>Teacher</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($homeworks as $homework)
                    @php
                        $submission = $homework->submissions->first();
                    @endphp
                    <tr>
                        <td class="fw-medium">{{ $homework->title }}</td>
                        <td>{{ $homework->subject->name ?? '' }}</td>
                        <td>{{ $homework->staff->user->name ?? '' }}</td>
                        <td>{{ $homework->due_date->format('d M, Y') }}</td>
                        <td>
                            @if($submission)
                                @if($submission->status == 'evaluated')
                                    <span class="badge bg-success">Evaluated ({{ $submission->marks }}/{{ $homework->max_marks }})</span>
                                @else
                                    <span class="badge bg-info">Submitted</span>
                                @endif
                            @else
                                <span class="badge bg-warning text-dark">Pending</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('student.homework.show', $homework->id) }}" class="btn btn-sm btn-primary">View / Submit</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No homework assignments found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3 border-top">
            {{ $homeworks->links() }}
        </div>
    </div>
</div>
@endsection
