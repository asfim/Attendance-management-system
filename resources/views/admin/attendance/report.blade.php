@extends('layouts.app')

@section('content')
<div class="card glass-card border-0 p-4">
    <h5 class="fw-bold mb-4">Attendance Report</h5>
    <form action="{{ route('admin.attendance.report') }}" method="GET" class="row g-3 mb-4 align-items-end">
        <div class="col-md-3">
            <label class="form-label fs-7">Class</label>
            <select name="class_id" class="form-select" required onchange="this.form.submit()">
                <option value="">Select Class</option>
                @foreach($classes as $c)
                    <option value="{{ $c->id }}" {{ request('class_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label fs-7">Section</label>
            <select name="section_id" class="form-select" required>
                <option value="">Select Section</option>
                @if(request()->filled('class_id'))
                    @foreach(\App\Models\Section::where('class_id', request('class_id'))->get() as $sec)
                        <option value="{{ $sec->id }}" {{ request('section_id') == $sec->id ? 'selected' : '' }}>{{ $sec->name }}</option>
                    @endforeach
                @endif
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label fs-7">Shift (Optional)</label>
            <select name="shift_id" class="form-select">
                <option value="">All Shifts</option>
                @foreach($shifts as $shift)
                    <option value="{{ $shift->id }}" {{ request('shift_id') == $shift->id ? 'selected' : '' }}>{{ $shift->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label fs-7">Date</label>
            <input type="date" name="date" class="form-control" value="{{ request('date', now()->format('Y-m-d')) }}" required>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">Load Report</button>
        </div>
    </form>

    @if(!empty($report))
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Roll</th>
                        <th>Name</th>
                        <th>Status</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($report as $att)
                        <tr>
                            <td>{{ $att->attendable->roll_no }}</td>
                            <td>{{ $att->attendable->user->name }}</td>
                            <td>
                                <span class="badge bg-{{ $att->status === 'present' ? 'success' : ($att->status === 'absent' ? 'danger' : 'warning') }}">
                                    {{ ucfirst($att->status) }}
                                </span>
                            </td>
                            <td>{{ $att->remarks ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
