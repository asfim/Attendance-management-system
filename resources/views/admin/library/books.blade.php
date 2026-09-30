@extends('layouts.app')

@section('content')
<div class="container-fluid px-0">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="fw-bold m-0">Book Catalog</h5>
            <p class="text-muted fs-7 mb-0">Manage and catalog library books.</p>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius: 12px;">
            <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius: 12px;">
            <i class="fa-solid fa-circle-exclamation me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius: 12px;">
            <h6 class="fw-bold mb-2"><i class="fa-solid fa-triangle-exclamation me-2"></i>Please fix the following errors:</h6>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Tab Navigation -->
    <ul class="nav nav-pills gap-2 mb-4 p-2 bg-light bg-opacity-10 rounded-3 shadow-sm border border-light border-opacity-10" id="booksTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active rounded-2 fw-bold" id="list-tab" data-bs-toggle="tab" data-bs-target="#list" type="button" role="tab" aria-controls="list" aria-selected="true">
                <i class="fa-solid fa-list me-2"></i>Book Catalog List
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-2 fw-bold" id="add-tab" data-bs-toggle="tab" data-bs-target="#add" type="button" role="tab" aria-controls="add" aria-selected="false">
                <i class="fa-solid fa-plus-circle me-2"></i>Add Book to Catalog
            </button>
        </li>
    </ul>

    <!-- Tab Contents -->
    <div class="tab-content" id="booksTabContent">
        <!-- Tab 1: Books List -->
        <div class="tab-pane fade show active" id="list" role="tabpanel" aria-labelledby="list-tab">
            <div class="card glass-card border-0 p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold text-primary m-0"><i class="fa-solid fa-list me-2"></i>Book Catalog List</h6>
                    <span class="badge bg-primary bg-opacity-10 text-primary">{{ count($books) }} Books</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Title & Author</th>
                                        <th>ISBN</th>
                                        <th>Publisher</th>
                                        <th>Rack No</th>
                                        <th class="text-center">Stock</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($books as $book)
                                        <tr>
                                            <td>
                                                <div class="fw-bold">{{ $book->title }}</div>
                                                <div class="text-muted fs-7">{{ $book->author }}</div>
                                            </td>
                                            <td><code class="text-dark bg-light px-2 py-1 rounded border">{{ $book->isbn }}</code></td>
                                            <td>{{ $book->publisher ?? '-' }}</td>
                                            <td><span class="badge bg-secondary bg-opacity-10 text-secondary">{{ $book->rack_no ?? 'N/A' }}</span></td>
                                            <td class="text-center">
                                                <span class="fw-bold {{ $book->available_qty > 0 ? 'text-success' : 'text-danger' }}">{{ $book->available_qty }}</span> / {{ $book->quantity }}
                                            </td>
                                            <td>
                                                @if($book->available_qty > 0)
                                                    <span class="badge bg-success bg-opacity-10 text-success">Available</span>
                                                @else
                                                    <span class="badge bg-danger bg-opacity-10 text-danger">Out of Stock</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-muted">No books found in the catalog.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                </div>
            </div>
        </div>

        <!-- Tab 2: Add Book Form -->
        <div class="tab-pane fade" id="add" role="tabpanel" aria-labelledby="add-tab">
            <div class="card glass-card border-0 p-4">
                <h6 class="fw-bold text-primary mb-4"><i class="fa-solid fa-plus-circle me-2"></i>Add Book to Catalog</h6>
                <form action="{{ route('admin.library.books.store') }}" method="POST">
                    @csrf
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label text-secondary small">Book Title</label>
                            <input type="text" name="title" class="form-control" placeholder="e.g. Introduction to Algorithms" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary small">Author Name</label>
                            <input type="text" name="author" class="form-control" placeholder="e.g. Thomas H. Cormen" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary small">ISBN (Unique)</label>
                            <input type="text" name="isbn" class="form-control" placeholder="e.g. 9780262033848" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary small">Publisher</label>
                            <input type="text" name="publisher" class="form-control" placeholder="e.g. MIT Press">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary small">Rack Number</label>
                            <input type="text" name="rack_no" class="form-control" placeholder="e.g. A-3">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary small">Total Copies</label>
                            <input type="number" name="quantity" class="form-control" placeholder="e.g. 5" min="1" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100"><i class="fa-solid fa-save me-2"></i>Add Book</button>
                        </form>
            </div>
        </div>
    </div>
</div>
@endsection
