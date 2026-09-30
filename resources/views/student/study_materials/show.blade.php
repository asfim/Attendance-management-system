@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold"><i class="fa-solid fa-book-open text-primary me-2"></i>Study Material Details</h4>
    <a href="{{ route('student.study-materials.index') }}" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-2"></i>Back</a>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
        <h5 class="fw-bold text-primary mb-3">{{ $material->title }}</h5>

        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <p class="text-muted mb-1 fs-7 text-uppercase">Subject</p>
                <p class="fw-medium mb-0">{{ $material->subject->name ?? 'N/A' }}</p>
            </div>
            <div class="col-md-3 mb-3">
                <p class="text-muted mb-1 fs-7 text-uppercase">Type</p>
                <p class="fw-medium mb-0">
                    @if($material->type == 'pdf')
                        <span class="badge bg-danger"><i class="fa-solid fa-file-pdf me-1"></i>PDF Document</span>
                    @elseif($material->type == 'document')
                        <span class="badge bg-primary"><i class="fa-solid fa-file-word me-1"></i>Word Document</span>
                    @elseif($material->type == 'video')
                        <span class="badge bg-danger"><i class="fa-solid fa-video me-1"></i>Video</span>
                    @elseif($material->type == 'image')
                        <span class="badge bg-success"><i class="fa-regular fa-image me-1"></i>Image</span>
                    @else
                        <span class="badge bg-info text-dark"><i class="fa-solid fa-link me-1"></i>Link</span>
                    @endif
                </p>
            </div>
            <div class="col-md-3 mb-3">
                <p class="text-muted mb-1 fs-7 text-uppercase">Uploaded By</p>
                <p class="fw-medium mb-0">{{ $material->staff->user->name ?? 'N/A' }}</p>
            </div>
            <div class="col-md-3 mb-3">
                <p class="text-muted mb-1 fs-7 text-uppercase">Date</p>
                <p class="fw-medium mb-0">{{ $material->created_at->format('d M, Y h:i A') }}</p>
            </div>
        </div>

        <div class="mb-4">
            <p class="text-muted mb-2 fs-7 text-uppercase">Description</p>
            <div class="p-3  rounded-3 border">
                @if($material->description)
                    {!! nl2br(e($material->description)) !!}
                @else
                    <span class="text-muted fst-italic">No description provided.</span>
                @endif
            </div>
        </div>

        <div class="d-flex align-items-center mt-4 pt-3 border-top">
            @if($material->type == 'link')
                @if($material->file_path)
                    <a href="{{ $material->file_path }}" target="_blank" class="btn btn-info text-white rounded-pill px-4 me-2">
                        <i class="fa-solid fa-arrow-up-right-from-square me-2"></i> Open Link
                    </a>
                @else
                    <button class="btn btn-secondary rounded-pill px-4 me-2" disabled>
                        <i class="fa-solid fa-ban me-2"></i> No Link Available
                    </button>
                @endif
            @else
                @if($material->file_path)
                    <a href="{{ asset('storage/' . $material->file_path) }}" target="_blank" class="btn btn-primary rounded-pill px-4 me-2">
                        <i class="fa-solid fa-download me-2"></i> Download File
                    </a>
                @else
                    <button class="btn btn-secondary rounded-pill px-4 me-2" disabled>
                        <i class="fa-solid fa-file-excel me-2"></i> No File Available
                    </button>
                @endif
            @endif
        </div>
    </div>
</div>
@endsection
