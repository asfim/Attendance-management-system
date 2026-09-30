@extends('layouts.app')

@section('content')
<div class="row g-4">
    <div class="col-md-6">
        <div class="card glass-card border-0 p-4">
            <h5 class="fw-bold mb-4">Create Certificate Template</h5>
            <form action="{{ route('admin.certificates.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <input type="text" name="template_name" class="form-control" placeholder="Template Name" required>
                </div>
                <div class="mb-3">
                    <textarea name="content" class="form-control" placeholder="HTML Content with placeholders {name}, {roll}, {class}" rows="5" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary w-100">Create Template</button>
            </form>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card glass-card border-0 p-4">
            <h5 class="fw-bold mb-4">Issue Certificate</h5>
            <form action="{{ route('admin.certificates.issue') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <select name="certificate_id" class="form-select" required>
                        <option value="">Select Template</option>
                        @foreach($templates as $temp)
                            <option value="{{ $temp->id }}">{{ $temp->template_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <select name="user_id" class="form-select" required>
                        <option value="">Select User</option>
                        @foreach($users as $usr)
                            <option value="{{ $usr->id }}">{{ $usr->name }} ({{ $usr->role->display_name }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <input type="date" name="issue_date" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
                </div>
                <button type="submit" class="btn btn-success w-100">Issue Certificate</button>
            </form>
        </div>
    </div>
</div>
@endsection
