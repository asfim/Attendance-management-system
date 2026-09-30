@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold"><i class="fa-solid fa-book-open text-primary me-2"></i>All Homework Assignments</h4>
</div>

<div class="card glass-card border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>Title</th>
                        <th>Class & Section</th>
                        <th>Subject</th>
                        <th>Teacher</th>
                        <th>Due Date</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($homeworks as $homework)
                    <tr>
                        <td class="fw-medium">{{ $homework->title }}</td>
                        <td>{{ $homework->schoolClass->name ?? '' }} - {{ $homework->section->name ?? '' }}</td>
                        <td>{{ $homework->subject->name ?? '' }}</td>
                        <td>{{ $homework->staff->user->name ?? 'Unknown' }}</td>
                        <td>
                            @if($homework->due_date < now()->toDateString())
                                <span class="text-danger"><i class="fa-regular fa-calendar-xmark me-1"></i>{{ $homework->due_date->format('d M, Y') }}</span>
                            @else
                                <span class="text-success"><i class="fa-regular fa-calendar-check me-1"></i>{{ $homework->due_date->format('d M, Y') }}</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <form action="{{ route('admin.homework.destroy', $homework->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Are you sure you want to delete this homework?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger"><i class="fa-regular fa-trash-can"></i></button>
                            </form>
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
