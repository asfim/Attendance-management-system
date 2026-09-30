@extends('layouts.app')

@section('content')
<div class="row g-4 mb-4">
    <!-- Parent Details -->
    <div class="col-lg-4">
        <div class="card glass-card p-4 border-0">
            <h5 class="fw-bold mb-3"><i class="fa-solid fa-id-card text-primary me-2"></i>Parent Profile</h5>
            <div class="text-center py-3">
                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-2" style="width: 60px; height: 60px; font-size: 1.5rem; font-weight: 700;">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <h6 class="fw-bold mb-1">{{ auth()->user()->name }}</h6>
                <p class="text-secondary fs-7 mb-0">{{ auth()->user()->email }}</p>
            </div>
            <div class="border-top border-light border-opacity-10 pt-3">
                <div class="mb-2"><span class="text-secondary fw-semibold">Occupation:</span> <span class="float-end">{{ $parent->occupation ?? 'N/A' }}</span></div>
                <div><span class="text-secondary fw-semibold">Phone:</span> <span class="float-end">{{ $parent->phone ?? 'N/A' }}</span></div>
            </div>
        </div>
    </div>

    <!-- Active Noticeboard -->
    <div class="col-lg-8">
        <div class="card glass-card p-4 border-0" style="min-height: 250px;">
            <h5 class="fw-bold mb-3"><i class="fa-solid fa-bullhorn text-primary me-2"></i>School Announcements</h5>
            <div class="list-group list-group-flush">
                @forelse($notices as $notice)
                    <div class="list-group-item bg-transparent px-0 border-light border-opacity-10 py-3">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1 fw-semibold text-secondary">{{ $notice->title }}</h6>
                            <small class="text-muted">{{ $notice->published_at->format('M d, Y') }}</small>
                        </div>
                        <p class="mb-0 text-muted fs-7">{{ $notice->content }}</p>
                    </div>
                @empty
                    <p class="text-muted text-center my-4">No active notices found.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="card glass-card border-0 p-4">
    <h5 class="fw-bold mb-4"><i class="fa-solid fa-user-graduate text-success me-2"></i>My Children</h5>
    <div class="row g-3">
        @forelse($children as $child)
            <div class="col-md-6">
                <div class="p-3 border rounded shadow-sm bg-light bg-opacity-10">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h6 class="fw-bold text-primary m-0">{{ $child->user->name }}</h6>
                            <small class="text-secondary">Class: {{ $child->schoolClass->name }} | Section: {{ $child->section->name }}</small>
                        </div>
                        <span class="badge bg-light text-dark fw-bold border">Roll: {{ $child->roll_no }}</span>
                    </div>
                    <div class="border-top border-light border-opacity-10 pt-3">
                        <a href="{{ route('parent.child.details', $child->id) }}" class="btn btn-sm btn-outline-primary w-100"><i class="fa-solid fa-square-poll-vertical me-2"></i>View Academic & Fee Records</a>
                    </div>
                </div>
            </div>
        @empty
            <p class="text-muted col-12 text-center my-4">No children profiles linked to this account.</p>
        @endforelse
    </div>
</div>
@endsection
