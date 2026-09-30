@extends('layouts.app')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('admin.finance.donations.index') }}" class="btn btn-sm btn-outline-secondary mb-2"><i class="fa-solid fa-arrow-left me-1"></i>Back to Donations</a>
            <h4 class="fw-bold m-0">Donation Campaigns</h4>
        </div>
        <div>
            <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addCampaignModal">
                <i class="fa-solid fa-plus me-2"></i>New Campaign
            </button>
        </div>
    </div>

    <div class="row g-4">
        @forelse($campaigns as $campaign)
            @php
                $collected = $campaign->donations()->sum('amount');
                $goal = $campaign->goal_amount;
                $percentage = $goal > 0 ? min(100, round(($collected / $goal) * 100)) : ($collected > 0 ? 100 : 0);
                
                $statusColor = 'success';
                if($campaign->status == 'cancelled') $statusColor = 'danger';
                if($campaign->status == 'completed') $statusColor = 'primary';
            @endphp
            <div class="col-md-6 col-xl-4">
                <div class="card glass-card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h5 class="fw-bold text-primary mb-0">{{ $campaign->name }}</h5>
                            <span class="badge bg-{{ $statusColor }} text-white text-uppercase fs-8">{{ $campaign->status }}</span>
                        </div>
                        
                        <p class="text-muted fs-7 mb-4" style="min-height: 40px;">
                            {{ Str::limit($campaign->description ?? 'No description provided.', 80) }}
                        </p>
                        
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1 fs-7">
                                <span class="fw-semibold">Collected: ৳{{ number_format($collected, 2) }}</span>
                                <span class="text-muted">Goal: ৳{{ number_format($goal, 2) }}</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-success" role="progressbar" style="width: {{ $percentage }}%;" aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top border-light">
                            <div class="text-muted fs-8">
                                <i class="fa-regular fa-calendar me-1"></i>
                                Ends: {{ $campaign->end_date ? $campaign->end_date->format('d M, Y') : 'No End Date' }}
                            </div>
                            <div>
                                <form action="{{ route('admin.finance.donations.campaigns.update', $campaign->id) }}" method="POST" class="d-inline-block me-1">
                                    @csrf
                                    @method('PUT')
                                    <select name="status" class="form-select form-select-sm d-inline-block w-auto py-0" onchange="this.form.submit()">
                                        <option value="active" {{ $campaign->status == 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="completed" {{ $campaign->status == 'completed' ? 'selected' : '' }}>Completed</option>
                                        <option value="cancelled" {{ $campaign->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                    </select>
                                </form>
                                <form action="{{ route('admin.finance.donations.campaigns.destroy', $campaign->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Delete this campaign? Only possible if no donations exist.');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger border-0 py-0"><i class="fa-solid fa-trash"></i></button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card glass-card border-0 shadow-sm text-center py-5">
                    <i class="fa-solid fa-bullseye fs-1 text-muted opacity-25 mb-3"></i>
                    <h5 class="text-muted">No campaigns running</h5>
                </div>
            </div>
        @endforelse
    </div>
</div>

<!-- Add Campaign Modal -->
<div class="modal fade" id="addCampaignModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <h5 class="modal-title fw-bold">Create Campaign</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.finance.donations.campaigns.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Campaign Name *</label>
                        <input type="text" name="name" class="form-control" required placeholder="e.g. Mosque Construction Fund">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Goal Amount (৳) *</label>
                        <input type="number" step="0.01" name="goal_amount" class="form-control" required placeholder="0.00">
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Start Date</label>
                            <input type="date" name="start_date" class="form-control">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">End Date</label>
                            <input type="date" name="end_date" class="form-control">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 pb-4 px-4">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4">Create Campaign</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
