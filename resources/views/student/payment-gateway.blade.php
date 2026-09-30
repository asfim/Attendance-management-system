@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card glass-card border-0 p-4">
            <h5 class="fw-bold mb-4 text-center">Secure Payment Checkout</h5>

            <!-- Invoice Summary Card -->
            <div class="p-3 border rounded mb-4 bg-light bg-opacity-10 text-center">
                <span class="text-secondary fw-semibold">Invoice Number</span>
                <h6 class="fw-bold text-primary mb-3">{{ $invoice->invoice_number }}</h6>
                <div class="row">
                    <div class="col border-end">
                        <span class="text-secondary fs-7">Total Invoiced</span>
                        <div class="fw-bold fs-5">৳{{ number_format($invoice->grand_total, 2) }}</div>
                    </div>
                    <div class="col">
                        <span class="text-secondary fs-7">Balance Due</span>
                        <div class="fw-bold fs-5 text-danger">৳{{ number_format($invoice->remaining_amount, 2) }}</div>
                    </div>
                </div>
            </div>

            <form action="{{ route('student.fees.pay.process', $invoice->id) }}" method="POST">
                @csrf
                <input type="hidden" name="amount" value="{{ $invoice->remaining_amount }}">

                <h6 class="fw-semibold text-secondary mb-3">Payment Gateway</h6>
                
                <div class="d-flex flex-column gap-2 mb-4">
                    <!-- SSLCommerz -->
                    <label class="d-flex align-items-center justify-content-between p-3 border rounded cursor-pointer hover-scale border-primary bg-primary bg-opacity-10 mb-0">
                        <div class="d-flex align-items-center gap-3">
                            <input type="radio" name="payment_method" value="SSLCommerz" checked>
                            <span class="fw-semibold text-primary">SSLCommerz (Cards, Mobile Banking, Net Banking)</span>
                        </div>
                        <i class="fa-solid fa-credit-card text-primary fa-lg"></i>
                    </label>
                </div>

                <button type="submit" class="btn btn-success w-100 py-3 fw-bold fs-6" style="border-radius: 12px;">
                    <i class="fa-solid fa-shield-halved me-2"></i>Pay ৳{{ number_format($invoice->remaining_amount, 2) }} with SSLCommerz
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
