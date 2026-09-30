@extends('layouts.app')

@section('content')
<div class="row g-4">
    <div class="col-md-6">
        <div class="card glass-card border-0 p-4">
            <h5 class="fw-bold mb-4">Allocate Transport Route</h5>
            <form action="{{ route('admin.transport.allocate') }}" method="POST">
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
                    <label class="form-label text-secondary">Select Route</label>
                    <select name="route_id" class="form-select" required>
                        <option value="">Select Route</option>
                        @foreach($routes as $rt)
                            <option value="{{ $rt->id }}">{{ $rt->route_name }} (Fare: ${{ number_format($rt->fare, 2) }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label text-secondary">Select Vehicle</label>
                    <select name="vehicle_id" class="form-select" required>
                        <option value="">Select Vehicle</option>
                        @foreach($vehicles as $vh)
                            <option value="{{ $vh->id }}">{{ $vh->vehicle_no }} ({{ $vh->vehicle_model }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label text-secondary">Select Driver</label>
                    <select name="driver_id" class="form-select" required>
                        <option value="">Select Driver</option>
                        @foreach($drivers as $dr)
                            <option value="{{ $dr->id }}">{{ $dr->name }} ({{ $dr->phone }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label text-secondary">Allocation Date</label>
                    <input type="date" name="allocation_date" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Allocate Route</button>
            </form>
        </div>
    </div>
</div>
@endsection
