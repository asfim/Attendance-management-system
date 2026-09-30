@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card glass-card border-0 p-4">
            <h5 class="fw-bold mb-4">Register New Teacher</h5>
            <form action="{{ route('admin.teachers.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label text-secondary">Full Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label text-secondary">Email Address</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label text-secondary">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label text-secondary">Phone</label>
                    <input type="text" name="phone" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label text-secondary">Address</label>
                    <input type="text" name="address" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label text-secondary">Qualification</label>
                    <input type="text" name="qualification" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label text-secondary">Designation</label>
                    <input type="text" name="designation" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label text-secondary">Joining Date</label>
                    <input type="date" name="joining_date" class="form-control" required>
                </div>
                <div class="mb-4">
                    <label class="form-label text-secondary">Monthly Salary ($)</label>
                    <input type="number" name="salary" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100 py-2">Register Teacher</button>
            </form>
        </div>
    </div>
</div>
@endsection
