@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold"><i class="fa-solid fa-book-open text-primary me-2"></i>Homework Details</h4>
    <a href="{{ route('student.homework.index') }}" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-2"></i>Back to List</a>
</div>

<div class="row g-4">
    <!-- Homework Information -->
    <div class="col-lg-7">
        <div class="card glass-card border-0 mb-4 h-100">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <h5 class="fw-bold text-primary mb-0">{{ $homework->title }}</h5>
                    <span class="badge bg-light text-dark border">{{ $homework->subject->name ?? 'Subject' }}</span>
                </div>
                
                <div class="mb-4">
                    <span class="text-muted d-block mb-1"><i class="fa-regular fa-calendar me-2"></i>Due Date: <span class="fw-medium text-danger">{{ $homework->due_date->format('d M, Y') }}</span></span>
                    <span class="text-muted d-block mb-1"><i class="fa-solid fa-user-tie me-2"></i>Teacher: <span class="fw-medium text-light">{{ $homework->staff->user->name ?? 'N/A' }}</span></span>
                    <span class="text-muted d-block"><i class="fa-solid fa-star me-2"></i>Max Marks: <span class="fw-medium text-light">{{ $homework->max_marks ?? 'Not Specified' }}</span></span>
                </div>
                
                <h6 class="fw-bold mb-2 border-bottom border-secondary border-opacity-25 pb-2">Description</h6>
                <div class="mb-4 text-light" style="white-space: pre-wrap;">{{ $homework->description ?? 'No description provided.' }}</div>
                
                @if($homework->file_path)
                    <h6 class="fw-bold mb-2">Attachment</h6>
                    <a href="{{ asset('storage/' . $homework->file_path) }}" target="_blank" class="btn btn-sm btn-outline-info rounded-pill px-3">
                        <i class="fa-solid fa-download me-2"></i>Download Attachment
                    </a>
                @endif
            </div>
        </div>
    </div>

    <!-- Submission Section -->
    <div class="col-lg-5">
        <div class="card glass-card border-0 h-100">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-4 border-bottom border-secondary border-opacity-25 pb-2"><i class="fa-solid fa-file-arrow-up text-success me-2"></i>Your Submission</h5>
                
                @if($submission)
                    <!-- Already Submitted -->
                    <div class="alert alert-success bg-success bg-opacity-10 border-success border-opacity-25 text-success">
                        <i class="fa-solid fa-circle-check me-2"></i>You have already submitted this homework.
                    </div>
                    
                    <div class="mb-3">
                        <span class="text-muted d-block mb-1">Submitted On: <strong class="text-light">{{ $submission->created_at->format('d M, Y h:i A') }}</strong></span>
                        <span class="text-muted d-block mb-1">Status: 
                            @if($submission->status == 'evaluated')
                                <strong class="text-success">Evaluated</strong>
                            @else
                                <strong class="text-info">Pending Evaluation</strong>
                            @endif
                        </span>
                        
                        @if($submission->status == 'evaluated')
                            <span class="text-muted d-block mb-1">Marks Obtained: <strong class="text-success">{{ $submission->marks }} / {{ $homework->max_marks }}</strong></span>
                            @if($submission->teacher_remarks)
                                <div class="mt-3 p-3 bg-dark bg-opacity-50 rounded border border-secondary border-opacity-25">
                                    <h6 class="fw-bold text-warning mb-1">Teacher's Feedback:</h6>
                                    <p class="mb-0 text-light">{{ $submission->teacher_remarks }}</p>
                                </div>
                            @endif
                        @endif
                    </div>
                    
                    <div class="mb-3 mt-4">
                        <h6 class="fw-bold mb-2">Your Answer:</h6>
                        <div class="p-3 bg-dark bg-opacity-25 rounded border border-secondary border-opacity-10 text-light" style="white-space: pre-wrap;">{{ $submission->student_remarks }}</div>
                    </div>
                    
                    @if($submission->file_path)
                        <div class="mt-3">
                            <h6 class="fw-bold mb-2">Attached File:</h6>
                            <a href="{{ asset('storage/' . $submission->file_path) }}" target="_blank" class="btn btn-sm btn-outline-info rounded-pill px-3">
                                <i class="fa-solid fa-download me-2"></i>Download Your File
                            </a>
                        </div>
                    @endif
                    
                @else
                    <!-- Submission Form -->
                    <form action="{{ route('student.homework.submit', $homework->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="mb-3">
                            <label class="form-label text-light">Answer Text <span class="text-danger">*</span></label>
                            <textarea name="student_remarks" class="form-control bg-transparent text-light border-secondary border-opacity-50" rows="5" placeholder="Type your answer or comments here..." required></textarea>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label text-light">Attach File (Optional)</label>
                            <input type="file" name="file" class="form-control bg-transparent text-light border-secondary border-opacity-50" accept=".pdf,.doc,.docx,.jpg,.png,.zip">
                            <small class="text-muted mt-1 d-block">Max size: 5MB</small>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-success py-2 rounded-pill fw-bold">
                                <i class="fa-solid fa-paper-plane me-2"></i>Submit Homework
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
