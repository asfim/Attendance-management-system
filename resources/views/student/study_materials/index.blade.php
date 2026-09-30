@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold"><i class="fa-solid fa-folder-open text-primary me-2"></i>Study Materials</h4>
</div>

<div class="card glass-card border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Subject</th>
                        <th>Teacher</th>
                        <th>Uploaded On</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($materials as $material)
                    <tr>
                        <td class="fw-medium">{{ $material->title }}
                            @if($material->description)
                            <div class="text-muted fs-7 mt-1">{{ \Illuminate\Support\Str::limit($material->description, 50) }}</div>
                            @endif
                        </td>
                        <td>
                            @if($material->type == 'pdf')
                                <span class="badge bg-danger"><i class="fa-regular fa-file-pdf me-1"></i>PDF</span>
                            @elseif($material->type == 'document')
                                <span class="badge bg-primary"><i class="fa-regular fa-file-word me-1"></i>Document</span>
                            @elseif($material->type == 'video')
                                <span class="badge bg-danger"><i class="fa-solid fa-video me-1"></i>Video</span>
                            @elseif($material->type == 'image')
                                <span class="badge bg-success"><i class="fa-regular fa-image me-1"></i>Image</span>
                            @else
                                <span class="badge bg-info text-dark"><i class="fa-solid fa-link me-1"></i>Link</span>
                            @endif
                        </td>
                        <td>{{ $material->subject->name ?? 'N/A' }}</td>
                        <td>{{ $material->staff->user->name ?? 'N/A' }}</td>
                        <td>{{ $material->created_at->format('d M, Y') }}</td>
                        <td class="text-end">
                            <a href="{{ route('student.study-materials.show', $material->id) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 me-1"><i class="fa-regular fa-eye me-1"></i> View</a>
                            @if($material->type == 'link')
                                @if($material->file_path)
                                    <a href="{{ $material->file_path }}" target="_blank" class="btn btn-sm btn-info text-white rounded-pill px-3"><i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Open</a>
                                @else
                                    <span class="text-muted"><i class="fa-solid fa-ban me-1"></i> No Link</span>
                                @endif
                            @else
                                @if($material->file_path)
                                    <a href="{{ asset('storage/' . $material->file_path) }}" target="_blank" class="btn btn-sm btn-primary rounded-pill px-3"><i class="fa-solid fa-download me-1"></i> Download</a>
                                @else
                                    <span class="text-muted"><i class="fa-solid fa-file-excel me-1"></i> No File</span>
                                @endif
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No study materials available.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3 border-top">
            {{ $materials->links() }}
        </div>
    </div>
</div>
@endsection
