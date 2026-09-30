<div class="modal-header border-bottom border-light">
    <h5 class="modal-title fw-bold">Attendance History: <span class="text-primary">{{ $student->user->name }}</span></h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body p-0">
    @php
        $totalPresent = $student->attendances->where('status', 'present')->count();
        $totalAbsent = $student->attendances->where('status', 'absent')->count();
        $totalLate = $student->attendances->where('status', 'late')->count();
    @endphp
    
    <div class="p-3 bg-white border-bottom border-light">
        <div class="row g-2 text-center">
            <div class="col-4">
                <div class="p-2 rounded" style="background: rgba(34,197,94,0.1); border: 1px solid rgba(34,197,94,0.2);">
                    <div class="text-success fw-bold fs-5">{{ $totalPresent }}</div>
                    <div class="text-success" style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px;">Present</div>
                </div>
            </div>
            <div class="col-4">
                <div class="p-2 rounded" style="background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.2);">
                    <div class="text-danger fw-bold fs-5">{{ $totalAbsent }}</div>
                    <div class="text-danger" style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px;">Absent</div>
                </div>
            </div>
            <div class="col-4">
                <div class="p-2 rounded" style="background: rgba(245,158,11,0.1); border: 1px solid rgba(245,158,11,0.2);">
                    <div class="text-warning fw-bold fs-5">{{ $totalLate }}</div>
                    <div class="text-warning" style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px;">Late</div>
                </div>
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light sticky-top">
                <tr>
                    <th class="ps-4">Date</th>
                    <th>Status</th>
                    <th class="pe-4">Remarks</th>
                </tr>
            </thead>
            <tbody>
                @forelse($student->attendances as $att)
                    <tr>
                        <td class="ps-4 text-nowrap fw-medium">{{ $att->attendance_date->format('d M, Y') }}</td>
                        <td>
                            @if($att->status === 'present')
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1"><i class="fa-solid fa-check me-1"></i>Present</span>
                            @elseif($att->status === 'absent')
                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-1"><i class="fa-solid fa-xmark me-1"></i>Absent</span>
                            @elseif($att->status === 'late')
                                <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-2 py-1"><i class="fa-solid fa-clock me-1"></i>Late</span>
                            @else
                                <span class="badge bg-secondary">{{ ucfirst($att->status) }}</span>
                            @endif
                        </td>
                        <td class="pe-4 text-muted fs-7">{{ $att->remarks ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center text-muted py-5">
                            <i class="fa-solid fa-calendar-xmark fs-2 mb-2 d-block text-secondary opacity-50"></i>
                            No attendance history found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="modal-footer bg-light border-top-0">
    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
</div>
