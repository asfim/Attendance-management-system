@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold"><i class="fa-solid fa-file-pdf text-primary me-2"></i>My Study Materials</h4>
    <a href="{{ route('teacher.study-materials.create') }}" class="btn btn-primary"><i class="fa-solid fa-plus me-2"></i>Upload Material</a>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="py-3 px-4 border-0 text-uppercase fs-7">Title</th>
                        <th class="py-3 border-0 text-uppercase fs-7">Type</th>
                        <th class="py-3 border-0 text-uppercase fs-7">Class & Section</th>
                        <th class="py-3 border-0 text-uppercase fs-7">Subject</th>
                        <th class="py-3 px-4 border-0 text-uppercase fs-7 text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($materials as $material)
                    <tr class="border-bottom">
                        <td class="px-4 py-3 fw-medium">{{ $material->title }}</td>
                        <td class="py-3"><span class="badge bg-secondary text-uppercase">{{ $material->type }}</span></td>
                        <td class="py-3">{{ $material->schoolClass->name ?? '' }} - {{ $material->section->name ?? '' }}</td>
                        <td class="py-3">{{ $material->subject->name ?? '' }}</td>
                        <td class="px-4 py-3 text-end">
                            @if($material->file_path)
                                @if(filter_var($material->file_path, FILTER_VALIDATE_URL))
                                    <a href="{{ $material->file_path }}" target="_blank" class="btn btn-sm btn-info text-white rounded-pill px-3"><i class="fa-solid fa-link me-1"></i> Open Link</a>
                                @else
                                    <a href="{{ asset('storage/' . $material->file_path) }}" target="_blank" class="btn btn-sm btn-info text-white rounded-pill px-3"><i class="fa-solid fa-download me-1"></i> Download</a>
                                @endif
                            @endif
                            <a href="{{ route('teacher.study-materials.edit', $material->id) }}" class="btn btn-sm btn-warning text-dark rounded-circle ms-1"><i class="fa-solid fa-pen-to-square"></i></a>
                            <form action="{{ route('teacher.study-materials.destroy', $material->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Are you sure you want to delete this study material?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger rounded-circle ms-1"><i class="fa-regular fa-trash-can"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-5">
                            <i class="fa-solid fa-folder-open fs-2 mb-3 d-block opacity-50"></i>
                            No study materials uploaded yet.
                        </td>
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
