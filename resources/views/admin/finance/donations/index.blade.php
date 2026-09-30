@extends('layouts.app')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold m-0"><i class="fa-solid fa-hand-holding-heart text-primary me-2"></i>Donations</h4>
            <p class="text-muted fs-7 mb-0">Record and manage received donations</p>
        </div>
        <div>
            <a href="{{ route('admin.finance.donations.campaigns') }}" class="btn btn-outline-secondary shadow-sm me-2">Donation Campaigns</a>
            <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addDonationModal">
                <i class="fa-solid fa-plus me-2"></i>Add Donation
            </button>
        </div>
    </div>

    <!-- Filter -->
    <div class="card glass-card mb-4 border-0 shadow-sm">
        <div class="card-body p-4">
            <form action="{{ route('admin.finance.donations.index') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fs-7 fw-semibold text-muted">Campaign</label>
                    <select name="campaign_id" class="form-select">
                        <option value="">All Campaigns / General</option>
                        @foreach($campaigns as $campaign)
                            <option value="{{ $campaign->id }}" {{ request('campaign_id') == $campaign->id ? 'selected' : '' }}>
                                {{ $campaign->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="fa-solid fa-filter me-2"></i>Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary & Data Table -->
    <div class="card glass-card border-0 shadow-sm">
        <div class="card-header bg-transparent border-bottom-0 pt-4 pb-0 px-4 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0">Donation Records</h6>
            <h6 class="fw-bold mb-0 text-primary">Total: ৳{{ number_format($totalDonations, 2) }}</h6>
        </div>
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Donor Name</th>
                            <th>Type / Campaign</th>
                            <th>Method</th>
                            <th>Ref No</th>
                            <th class="text-end">Amount</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($donations as $donation)
                        <tr>
                            <td>{{ $donation->donation_date->format('d M, Y') }}</td>
                            <td class="fw-medium text-primary">
                                {{ $donation->donor_name }}<br>
                                <span class="fs-8 text-muted">{{ $donation->donor_phone }}</span>
                            </td>
                            <td>
                                <span class="badge bg-info bg-opacity-10 text-info border">{{ $donation->type }}</span>
                                @if($donation->campaign)
                                    <br><span class="fs-8 text-muted"><i class="fa-solid fa-bullseye me-1"></i>{{ $donation->campaign->name }}</span>
                                @endif
                            </td>
                            <td>{{ $donation->payment_method ?? '-' }}</td>
                            <td>{{ $donation->reference_no ?? '-' }}</td>
                            <td class="text-end fw-bold text-success">৳{{ number_format($donation->amount, 2) }}</td>
                            <td class="text-end">
                                @if($donation->receipt_path)
                                <a href="{{ asset('storage/' . $donation->receipt_path) }}" target="_blank" class="btn btn-sm btn-outline-info border-0 me-1" title="View Receipt"><i class="fa-solid fa-file-invoice"></i></a>
                                @endif
                                <form action="{{ route('admin.finance.donations.destroy', $donation->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this donation record?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger border-0"><i class="fa-solid fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-hand-holding-heart fs-2 mb-3 opacity-25"></i>
                                <h5>No donations found</h5>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $donations->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Add Donation Modal -->
<div class="modal fade" id="addDonationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <h5 class="modal-title fw-bold">Record Donation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.finance.donations.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Donor Name *</label>
                            <input type="text" name="donor_name" class="form-control" required placeholder="Full Name or 'Anonymous'">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Donor Phone</label>
                            <input type="text" name="donor_phone" class="form-control" placeholder="Optional">
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Amount (৳) *</label>
                            <input type="number" step="0.01" name="amount" class="form-control" required placeholder="0.00">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Donation Date *</label>
                            <input type="date" name="donation_date" class="form-control" required value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Type *</label>
                            <select name="type" class="form-select" required>
                                <option value="General">General / Lillah</option>
                                <option value="Zakat">Zakat</option>
                                <option value="Sadaqah">Sadaqah</option>
                                <option value="Fitrah">Fitrah</option>
                                <option value="Waqf">Waqf</option>
                            </select>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Link to Campaign (Optional)</label>
                            <select name="campaign_id" class="form-select">
                                <option value="">None (General Fund)</option>
                                @foreach($campaigns as $campaign)
                                    <option value="{{ $campaign->id }}">{{ $campaign->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Payment Method</label>
                            <select name="payment_method" class="form-select">
                                <option value="Cash">Cash</option>
                                <option value="Bank Transfer">Bank Transfer</option>
                                <option value="Mobile Banking">Mobile Banking</option>
                                <option value="Cheque">Cheque</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Reference No / TrxID</label>
                            <input type="text" name="reference_no" class="form-control" placeholder="Optional">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Upload Receipt</label>
                            <input type="file" name="receipt_path" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label fw-semibold">Notes</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Any specific wishes or notes from donor"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 pb-4 px-4">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4">Save Donation</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
