@extends('layouts.app')

@section('content')
<div class="container-fluid p-0">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-light"><i class="fa-solid fa-clock-rotate-left text-primary me-2"></i>Payroll History</h4>
            <p class="text-muted fs-7 mb-0">Select a staff member to view their complete salary and attendance history.</p>
        </div>
    </div>

    <!-- Staff List -->
    <div class="card glass-card border-0 shadow-sm rounded-4 mb-4">
        <div class="table-responsive">
            <table class="table align-middle text-light mb-0" style="--bs-table-bg: transparent; --bs-table-border-color: rgba(255,255,255,0.05);">
                <thead style="background-color: rgba(0,0,0,0.2);">
                    <tr>
                        <th class="text-uppercase fs-7 text-muted fw-semibold py-3 px-4 border-0">Staff Member</th>
                        <th class="text-uppercase fs-7 text-muted fw-semibold py-3 border-0">Role & Designation</th>
                        <th class="text-uppercase fs-7 text-muted fw-semibold py-3 border-0">Phone</th>
                        <th class="text-uppercase fs-7 text-muted fw-semibold py-3 px-4 border-0 text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($staffMembers as $staff)
                        <tr class="border-bottom border-secondary border-opacity-10 hover-bg-secondary transition-all">
                            <td class="px-4 py-3">
                                <div class="d-flex align-items-center">
                                    @if($staff->photo)
                                        <img src="{{ asset('storage/' . $staff->photo) }}" alt="Photo" class="rounded-circle object-fit-cover border border-secondary border-opacity-25 me-3" style="width: 40px; height: 40px;">
                                    @else
                                        <div class="bg-primary bg-opacity-25 text-primary rounded-circle d-flex justify-content-center align-items-center fw-bold me-3" style="width: 40px; height: 40px;">
                                            {{ strtoupper(substr($staff->user->name, 0, 1)) }}
                                        </div>
                                    @endif
                                    <div>
                                        <div class="fw-semibold text-light">{{ $staff->user->name }}</div>
                                        <div class="text-muted fs-7">#STF-{{ str_pad($staff->id, 4, '0', STR_PAD_LEFT) }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="text-light">{{ $staff->user->role ? $staff->user->role->display_name : 'Unassigned' }}</div>
                                <div class="text-muted fs-7">{{ $staff->designation ?? 'N/A' }}</div>
                            </td>
                            <td class="text-muted">
                                {{ $staff->phone ?? 'N/A' }}
                            </td>
                            <td class="text-end px-4">
                                <a href="{{ route('admin.payroll.history.show', $staff->id) }}" class="btn btn-sm btn-primary px-3 shadow-sm" style="border-radius: 8px;">
                                    <i class="fa-solid fa-eye me-1"></i> View History
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">No staff members found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($staffMembers->hasPages())
        <div class="card-footer bg-transparent border-top border-secondary border-opacity-25 p-3">
            {{ $staffMembers->links() }}
        </div>
        @endif
    </div>
</div>

<style>
    .hover-bg-secondary:hover {
        background-color: rgba(255, 255, 255, 0.05) !important;
    }
    .transition-all {
        transition: all 0.2s ease-in-out;
    }
</style>
@endsection
