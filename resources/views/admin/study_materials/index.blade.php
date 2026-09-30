@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold"><i class="fa-solid fa-file-pdf text-primary me-2"></i>All Study Materials</h4>
</div>

<div class="card glass-card border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Class & Section</th>
                        <th>Subject</th>
                        <th>Teacher</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($materials as $material)
                    <tr>
                        <td class="fw-medium">{{ $material->title }}</td>
                        <td><span class="badge bg-secondary text-uppercase">{{ $material->type }}</span></td>
                        <td>{{ $material->schoolClass->name ?? '' }} - {{ $material->section->name ?? '' }}</td>
                        <td>{{ $material->subject->name ?? '' }}</td>
                        <td>{{ $material->staff->user->name ?? 'Unknown' }}</td>
                        <td class="text-end">
                            @if($material->file_path)
                                @if(filter_var($material->file_path, FILTER_VALIDATE_URL))
                                    <a href="{{ $material->file_path }}" target="_blank" class="btn btn-sm btn-info text-white"><i class="fa-solid fa-link"></i></a>
                                @else
                                    <a href="{{ asset('storage/' . $material->file_path) }}" target="_blank" class="btn btn-sm btn-info text-white"><i class="fa-solid fa-download"></i></a>
                                @endif
                            @endif
                            <form action="{{ route('admin.study-materials.destroy', $material->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Are you sure you want to delete this study material?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger"><i class="fa-regular fa-trash-can"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No study materials found.</td>
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
