@extends('layouts.app')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold m-0"><i class="fa-solid fa-truck text-primary me-2"></i>Suppliers</h4>
            <p class="text-muted fs-7 mb-0">Manage Inventory Suppliers</p>
        </div>
    </div>

    <div class="row g-4">
        <!-- Add Supplier Card -->
        <div class="col-md-4">
            <div class="card glass-card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent py-3">
                    <h6 class="fw-bold m-0">Add Supplier</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.inventory.suppliers.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Supplier Name *</label>
                            <input type="text" name="name" class="form-control" required placeholder="e.g. ABC Electronics">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Phone</label>
                            <input type="text" name="phone" class="form-control" placeholder="Optional">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" name="email" class="form-control" placeholder="Optional">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Address</label>
                            <textarea name="address" class="form-control" rows="2" placeholder="Optional"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100"><i class="fa-solid fa-plus me-2"></i>Save Supplier</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Supplier List -->
        <div class="col-md-8">
            <div class="card glass-card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent py-3">
                    <h6 class="fw-bold m-0">Existing Suppliers</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle m-0">
                            <thead>
                                <tr>
                                    <th class="ps-4">Supplier Name</th>
                                    <th>Phone</th>
                                    <th>Email</th>
                                    <th>Address</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($suppliers as $supplier)
                                <tr>
                                    <td class="ps-4 fw-medium text-primary">{{ $supplier->name }}</td>
                                    <td>{{ $supplier->phone ?? '-' }}</td>
                                    <td>{{ $supplier->email ?? '-' }}</td>
                                    <td class="fs-8">{{ $supplier->address ?? '-' }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">No suppliers added yet.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
