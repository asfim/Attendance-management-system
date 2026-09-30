@extends('layouts.app')

@section('content')
<div class="row g-4">
    <div class="col-md-6">
        <div class="card glass-card border-0 p-4">
            <h5 class="fw-bold mb-4">Allocate Hostel Bed</h5>
            <form action="{{ route('admin.hostel.allocate') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label text-secondary">Select Student</label>
                    <select name="student_profile_id" class="form-select" required>
                        <option value="">Select Student</option>
                        @foreach($students as $st)
                            <option value="{{ $st->id }}">{{ $st->user->name }} (Roll: {{ $st->roll_no }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label text-secondary">Select Bed</label>
                    <select name="bed_id" class="form-select" required>
                        <option value="">Select Bed</option>
                        @foreach($hostels as $h)
                            @foreach($h->rooms as $r)
                                @foreach($r->beds as $b)
                                    @if($b->status === 'available')
                                        <option value="{{ $b->id }}">{{ $h->name }} - Room {{ $r->room_number }} - Bed {{ $b->bed_number }}</option>
                                    @endif
                                @endforeach
                            @endforeach
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label text-secondary">Allocation Date</label>
                    <input type="date" name="allocation_date" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Allocate Bed</button>
            </form>
        </div>
    </div>
</div>
@endsection
