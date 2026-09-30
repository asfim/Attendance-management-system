@extends('layouts.app')

@section('content')
<div class="row g-4 mb-4">
    <!-- Teacher Profile -->
    <div class="col-md-6">
        <div class="card glass-card p-0 border-0 overflow-hidden h-100">
            <div class="bg-primary bg-opacity-10 p-4 border-bottom border-primary border-opacity-10 d-flex align-items-center">
                @if($teacher->photo)
                    <img src="{{ asset('storage/' . $teacher->photo) }}" class="rounded-circle shadow me-3 object-fit-cover" style="width: 50px; height: 50px;" alt="Profile">
                @else
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center shadow me-3" style="width: 50px; height: 50px; font-size: 1.5rem;">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                @endif
                <div>
                    <h5 class="fw-bold mb-0 text-primary">{{ auth()->user()->name }}</h5>
                    <span class="badge bg-primary bg-opacity-10 text-primary mt-1">{{ $teacher->designation }}</span>
                </div>
            </div>
            <div class="p-4">
                <table class="table table-borderless mb-0">
                    <tr>
                        <td class="text-muted fw-medium pb-3" style="width: 40%;"><i class="fa-solid fa-graduation-cap me-2 text-primary opacity-75"></i>Qualification</td>
                        <td class="fw-semibold text-end pb-3">{{ $teacher->qualification }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted fw-medium pb-3"><i class="fa-solid fa-phone me-2 text-success opacity-75"></i>Contact</td>
                        <td class="fw-semibold text-end pb-3">{{ $teacher->phone }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted fw-medium pb-0"><i class="fa-solid fa-map-location-dot me-2 text-info opacity-75"></i>Address</td>
                        <td class="fw-semibold text-end pb-0 text-wrap">{{ $teacher->address }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="col-md-6">
        <div class="card glass-card p-4 border-0 h-100">
            <h5 class="fw-bold mb-4"><i class="fa-solid fa-bolt text-warning me-2"></i>Quick Actions</h5>

            <div class="d-grid gap-3">
                @php
                    $attendanceRoute = auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('menu_student_attendance')
                        ? route('admin.attendance.index')
                        : route('teacher.attendance');
                @endphp
                <a href="{{ $attendanceRoute }}" class="text-decoration-none">
                    <div class="d-flex align-items-center p-3 rounded-3 shadow-sm border border-primary border-opacity-25 quick-action-card" style="background: linear-gradient(135deg, rgba(var(--bs-primary-rgb), 0.1), rgba(var(--bs-primary-rgb), 0.02)); transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 6px 12px rgba(var(--bs-primary-rgb), 0.15)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 .125rem .25rem rgba(0,0,0,.075)'">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3 shadow-sm" style="width: 48px; height: 48px; flex-shrink: 0;">
                            <i class="fa-solid fa-user-check fs-5"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 fw-bold text-body">Take Student Attendance</h6>
                            <small class="text-muted">Mark daily presence and absence</small>
                        </div>
                        <div class="ms-auto bg-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 32px; height: 32px;">
                            <i class="fa-solid fa-chevron-right text-primary" style="font-size: 0.8rem;"></i>
                        </div>
                    </div>
                </a>

                @php
                    $marksRoute = auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('menu_academic_marks')
                        ? route('admin.marks.index')
                        : route('teacher.marks');
                @endphp
                <a href="{{ $marksRoute }}" class="text-decoration-none">
                    <div class="d-flex align-items-center p-3 rounded-3 shadow-sm border border-success border-opacity-25 quick-action-card" style="background: linear-gradient(135deg, rgba(var(--bs-success-rgb), 0.1), rgba(var(--bs-success-rgb), 0.02)); transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 6px 12px rgba(var(--bs-success-rgb), 0.15)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 .125rem .25rem rgba(0,0,0,.075)'">
                        <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-3 shadow-sm" style="width: 48px; height: 48px; flex-shrink: 0;">
                            <i class="fa-solid fa-square-poll-vertical fs-5"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 fw-bold text-body">Record Exam Marks</h6>
                            <small class="text-muted">Enter grades for class tests</small>
                        </div>
                        <div class="ms-auto bg-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 32px; height: 32px;">
                            <i class="fa-solid fa-chevron-right text-success" style="font-size: 0.8rem;"></i>
                        </div>
                    </div>
                </a>

                @php
                    $timetableRoute = auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('menu_academic_routine')
                        ? route('admin.routines.index')
                        : route('teacher.timetable');
                @endphp
                <a href="{{ $timetableRoute }}" class="text-decoration-none">
                    <div class="d-flex align-items-center p-3 rounded-3 shadow-sm border border-info border-opacity-25 quick-action-card" style="background: linear-gradient(135deg, rgba(var(--bs-info-rgb), 0.1), rgba(var(--bs-info-rgb), 0.02)); transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 6px 12px rgba(var(--bs-info-rgb), 0.15)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 .125rem .25rem rgba(0,0,0,.075)'">
                        <div class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center me-3 shadow-sm" style="width: 48px; height: 48px; flex-shrink: 0;">
                            <i class="fa-solid fa-calendar-days fs-5"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 fw-bold text-body">View My Timetable</h6>
                            <small class="text-muted">Check your weekly schedule</small>
                        </div>
                        <div class="ms-auto bg-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 32px; height: 32px;">
                            <i class="fa-solid fa-chevron-right text-info" style="font-size: 0.8rem;"></i>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Assigned Classes -->
<div class="card glass-card border-0 overflow-hidden">
    <div class="card-header bg-transparent border-0 p-4 pb-2">
        <h5 class="fw-bold mb-0"><i class="fa-solid fa-chalkboard-user text-primary me-2"></i>Assigned Classes</h5>
        <p class="text-muted fs-7 mb-0 mt-1">Classes where you are the designated class teacher.</p>
    </div>
    <div class="card-body p-4 pt-2">
        <div class="row g-4">
            @forelse($assignedClasses as $class)
                <div class="col-md-4">
                    <div class="position-relative overflow-hidden rounded-4 shadow-sm border border-primary border-opacity-10 h-100 p-4 d-flex flex-column" style="background: linear-gradient(135deg, rgba(var(--bs-primary-rgb), 0.05) 0%, transparent 100%); transition: transform 0.3s ease;" onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform='translateY(0)'">

                        <div class="position-absolute opacity-10" style="right: -10px; top: -10px; transform: rotate(15deg);">
                            <i class="fa-solid fa-chalkboard fs-1 text-primary"></i>
                        </div>

                        <div class="d-flex align-items-center mb-3 position-relative z-1">
                            <div class="bg-white rounded p-2 shadow-sm border border-light me-3">
                                <i class="fa-solid fa-users-rectangle fs-4 text-primary"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-0 ">{{ $class->schoolClass->name ?? 'Unknown Class' }}</h5>
                            </div>
                        </div>

                        <div class="mt-auto d-flex align-items-center justify-content-between position-relative z-1 pt-3 border-top border-secondary border-opacity-10 mb-2">
                            <span class="text-muted fs-7 fw-medium">Section</span>
                            <span class="badge bg-primary bg-opacity-10 text-primary fw-bold px-3 py-2 rounded-pill fs-7">{{ $class->section->name ?? 'Unknown' }}</span>
                        </div>

                        <div class="d-flex flex-column position-relative z-1 pt-2 border-top border-secondary border-opacity-10">
                            <span class="text-muted fs-7 fw-medium mb-1">Subjects</span>
                            <div class="d-flex flex-wrap gap-1">
                                @forelse($class->subjects as $subject)
                                    <span class="badge bg-light text-dark border border-secondary border-opacity-25 px-2 py-1" style="font-size: 0.7rem;">{{ $subject }}</span>
                                @empty
                                    <span class="text-muted" style="font-size: 0.7rem;">No subjects assigned</span>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="text-center p-5 border border-dashed rounded-4 bg-light bg-opacity-50">
                        <div class="bg-white rounded-circle d-inline-flex align-items-center justify-content-center shadow-sm mb-3" style="width: 64px; height: 64px;">
                            <i class="fa-solid fa-folder-open text-muted fs-3"></i>
                        </div>
                        <h6 class="fw-bold text-dark">No Classes Assigned</h6>
                        <p class="text-muted mb-0 fs-7">You have not been assigned as a class teacher yet.</p>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
