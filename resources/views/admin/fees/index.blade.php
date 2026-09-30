@extends('layouts.app')

@section('content')
<div class="row g-4 mb-4">
    <!-- Fee Categories Config -->
    <div class="col-md-6">
        <div class="card glass-card border-0 p-4">
            <h5 class="fw-bold mb-4">Fee Categories</h5>
            <form action="{{ route('admin.fees.categories.store') }}" method="POST" class="mb-4">
                @csrf
                <div class="row g-2">
                    <div class="col-8">
                        <input type="text" name="name" class="form-control" placeholder="e.g. Tuition Fee" required>
                    </div>
                    <div class="col-4">
                        <button type="submit" class="btn btn-primary w-100"><i class="fa-solid fa-plus me-2"></i>Add</button>
                    </div>
                </div>
            </form>
            <ul class="list-group">
                @foreach($categories as $cat)
                    <li class="list-group-item bg-transparent border-light border-opacity-10 text-white">
                        {{ $cat->name }}
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    <!-- Fee Structure Setup -->
    <div class="col-md-6">
        <div class="card glass-card border-0 p-4">
            <h5 class="fw-bold mb-4">Configure Fee Structure</h5>
            <form action="{{ route('admin.fees.structures.store') }}" method="POST" class="mb-4">
                @csrf
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <select name="fee_category_id" class="form-select" required>
                            <option value="">Select Category</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6">
                        <select name="class_id" class="form-select" required>
                            <option value="">Select Class</option>
                            @foreach($classes as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row g-2">
                    <div class="col-8">
                        <input type="number" name="amount" class="form-control" placeholder="Amount ($)" min="0" required>
                    </div>
                    <div class="col-4">
                        <button type="submit" class="btn btn-success w-100"><i class="fa-solid fa-floppy-disk me-2"></i>Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
