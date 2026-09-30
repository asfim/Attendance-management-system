@extends('layouts.app')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4 d-print-none">
        <div>
            <h4 class="fw-bold m-0"><i class="fa-solid fa-file-invoice-dollar text-primary me-2"></i>Donation Report</h4>
            <p class="text-muted fs-7 mb-0">Generate and print donation reports</p>
        </div>
        <div>
            <button onclick="window.print()" class="btn btn-secondary shadow-sm"><i class="fa-solid fa-print me-2"></i>Print Report</button>
        </div>
    </div>

    <div class="card glass-card mb-4 border-0 shadow-sm d-print-none">
        <div class="card-body p-4">
            <form action="{{ route('admin.finance.reports.donations') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
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
                <div class="col-md-3">
                    <label class="form-label fs-7 fw-semibold text-muted">Start Date</label>
                    <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fs-7 fw-semibold text-muted">End Date</label>
                    <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100"><i class="fa-solid fa-filter me-2"></i>Generate</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card bg-white border shadow-sm print-container">
        <div class="card-header bg-white border-bottom pt-4 pb-3 px-4 text-center d-print-none">
            <h5 class="fw-bold mb-1 text-uppercase">Donation Collection Report</h5>
            <p class="text-muted fs-8 mb-0">
                Period: {{ request('start_date') ? date('d M, Y', strtotime(request('start_date'))) : 'Beginning' }} 
                to {{ request('end_date') ? date('d M, Y', strtotime(request('end_date'))) : 'Current' }}
            </p>
        </div>
        <div class="card-body p-4">
            @include('admin.reports.partials.print_header', [
                'title' => 'Donation Collection Report',
                'subtitle' => 'Period: ' . (request('start_date') ? date('d M, Y', strtotime(request('start_date'))) : 'Beginning') . ' &mdash; ' . (request('end_date') ? date('d M, Y', strtotime(request('end_date'))) : 'Current')
            ])
            <div class="table-responsive mt-3">
                <table class="table table-bordered table-sm align-middle fs-7 text-dark">
                    <thead class="table-light text-dark">
                        <tr>
                            <th>Date</th>
                            <th>Donor Name</th>
                            <th>Type / Campaign</th>
                            <th>Method</th>
                            <th>Ref No</th>
                            <th class="text-end">Amount (৳)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($donations as $donation)
                        <tr>
                            <td>{{ $donation->donation_date->format('d/m/Y') }}</td>
                            <td>
                                {{ $donation->donor_name }}<br>
                                <span class="fs-8 text-muted">{{ $donation->donor_phone }}</span>
                            </td>
                            <td>
                                {{ $donation->type }}
                                @if($donation->campaign)
                                    <br><span class="fs-8 text-muted">{{ $donation->campaign->name }}</span>
                                @endif
                            </td>
                            <td>{{ $donation->payment_method ?? '-' }}</td>
                            <td>{{ $donation->reference_no ?? '-' }}</td>
                            <td class="text-end fw-medium">{{ number_format($donation->amount, 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">No donations found for the selected criteria.</td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="table-light fw-bold text-dark">
                            <td colspan="5" class="text-end">Total Collected:</td>
                            <td class="text-end text-success fs-6">৳{{ number_format($totalDonation, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
            <div class="mt-5 d-flex justify-content-between text-muted fs-8 d-print-none">
                <div>Generated on: {{ date('d M, Y h:i A') }}</div>
                <div>Authorized Signature _____________________</div>
            </div>
            @include('admin.reports.partials.print_footer')
        </div>
    </div>
</div>

<style>
@media print {
    body { background-color: #fff !important; }
    .sidebar, .navbar-custom, .d-print-none { display: none !important; }
    .main-content { margin: 0 !important; padding: 0 !important; }
    .print-container { box-shadow: none !important; border: none !important; }
    .table { color: #000 !important; }
}
</style>
@endsection
