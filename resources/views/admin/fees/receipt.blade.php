<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - {{ $invoice->invoice_number }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            color: #333;
            font-family: 'Inter', 'Segoe UI', sans-serif;
        }
        .receipt-container {
            max-width: 800px;
            margin: 40px auto;
            background: #fff;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        .receipt-header {
            border-bottom: 2px solid #eee;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .school-name {
            font-size: 24px;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        .school-address {
            color: #7f8c8d;
            font-size: 14px;
        }
        .receipt-title {
            font-size: 28px;
            font-weight: 800;
            color: #3498db;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .info-label {
            font-size: 12px;
            color: #95a5a6;
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 2px;
        }
        .info-value {
            font-size: 15px;
            font-weight: 600;
            color: #2c3e50;
        }
        .table th {
            background-color: #f8f9fa;
            color: #2c3e50;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 13px;
            border-bottom: 2px solid #dee2e6;
        }
        .table td {
            vertical-align: middle;
            color: #34495e;
            font-size: 15px;
        }
        .totals-row {
            background-color: #fcfcfc;
        }
        .grand-total {
            font-size: 20px;
            font-weight: 700;
            color: #2c3e50;
        }
        .status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 50px;
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-paid {
            background-color: #d4edda;
            color: #155724;
        }
        .status-due {
            background-color: #f8d7da;
            color: #721c24;
        }
        .print-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            border-radius: 50px;
            padding: 12px 25px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        
        @media print {
            body {
                background-color: #fff;
            }
            .receipt-container {
                box-shadow: none;
                margin: 0;
                padding: 20px;
                max-width: 100%;
            }
            .print-btn {
                display: none;
            }
        }
    </style>
</head>
<body>

    <div class="receipt-container">
        <!-- Header -->
        <div class="receipt-header d-flex justify-content-between align-items-center">
            <div>
                <div class="school-name">School Management System</div>
                <div class="school-address">123 Education Street, Learning City<br>Phone: +880 1234 567890 | Email: info@school.com</div>
            </div>
            <div class="text-end">
                <div class="receipt-title">RECEIPT</div>
                <div class="text-muted mt-1">#{{ $invoice->invoice_number }}</div>
            </div>
        </div>

        <!-- Student & Invoice Info -->
        <div class="row mb-4">
            <div class="col-sm-6">
                <div class="p-3 bg-light rounded-3">
                    <h6 class="border-bottom pb-2 mb-3 text-uppercase text-secondary fw-bold" style="font-size:13px;">Billed To</h6>
                    <div class="mb-2">
                        <div class="info-label">Student Name</div>
                        <div class="info-value">{{ $invoice->studentProfile->user->name ?? 'N/A' }}</div>
                    </div>
                    <div class="mb-2">
                        <div class="info-label">Class & Roll</div>
                        <div class="info-value">{{ $invoice->studentProfile->schoolClass->name ?? 'N/A' }} | Roll: {{ $invoice->studentProfile->roll_no ?? 'N/A' }}</div>
                    </div>
                    <div>
                        <div class="info-label">Student ID</div>
                        <div class="info-value">{{ $invoice->studentProfile->admission_no ?? 'N/A' }}</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 text-sm-end mt-4 mt-sm-0">
                <div class="mb-3">
                    <div class="info-label">Issue Date</div>
                    <div class="info-value">{{ $invoice->issue_date ? $invoice->issue_date->format('d F Y') : 'N/A' }}</div>
                </div>
                <div class="mb-3">
                    <div class="info-label">Due Date</div>
                    <div class="info-value">{{ $invoice->due_date ? $invoice->due_date->format('d F Y') : 'N/A' }}</div>
                </div>
                <div>
                    <div class="info-label mb-2">Payment Status</div>
                    @if ($invoice->grand_total - $invoice->paid_amount <= 0)
                        <div class="status-badge status-paid">PAID</div>
                    @else
                        <div class="status-badge status-due">DUE</div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Fee Details Table -->
        <h6 class="text-uppercase text-secondary fw-bold mb-3" style="font-size:13px;"><i class="fa-solid fa-list-check me-2"></i>Fee Details</h6>
        <div class="table-responsive mb-4">
            <table class="table table-borderless table-striped">
                <thead>
                    <tr>
                        <th style="width: 5%">#</th>
                        <th style="width: 45%">Fee Description</th>
                        <th class="text-end" style="width: 25%">Amount</th>
                        <th class="text-end" style="width: 25%">Discount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice->items as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <div class="fw-bold">{{ $item->feeCategory->name ?? 'Unknown Fee' }}</div>
                            <div class="text-muted" style="font-size: 13px;">{{ $item->installment_name }}</div>
                        </td>
                        <td class="text-end">৳{{ number_format($item->amount, 2) }}</td>
                        <td class="text-end text-danger">- ৳{{ number_format($item->discount_amount, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Totals Summary -->
        <div class="row">
            <div class="col-sm-6 offset-sm-6">
                <table class="table table-borderless table-sm mb-0">
                    <tr>
                        <td class="text-end text-secondary fw-semibold">Subtotal:</td>
                        <td class="text-end fw-semibold" style="width: 140px;">৳{{ number_format($invoice->subtotal, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="text-end text-secondary fw-semibold border-bottom pb-2">Total Discount:</td>
                        <td class="text-end text-danger fw-semibold border-bottom pb-2">- ৳{{ number_format($invoice->discount_amount, 2) }}</td>
                    </tr>
                    <tr class="totals-row">
                        <td class="text-end text-secondary fw-bold pt-3" style="font-size: 16px;">GRAND TOTAL:</td>
                        <td class="text-end grand-total pt-3">৳{{ number_format($invoice->grand_total, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="text-end text-secondary fw-semibold">Total Paid:</td>
                        <td class="text-end text-success fw-bold">৳{{ number_format($invoice->paid_amount, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="text-end text-secondary fw-bold">DUE AMOUNT:</td>
                        <td class="text-end text-danger fw-bold">৳{{ number_format(max(0, $invoice->grand_total - $invoice->paid_amount), 2) }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Payments History -->
        @if($invoice->payments->count() > 0)
        <div class="mt-5">
            <h6 class="border-bottom pb-2 mb-3 text-uppercase text-secondary fw-bold" style="font-size:13px;">Payment History</h6>
            <div class="table-responsive">
                <table class="table table-sm" style="font-size: 13px;">
                    <thead>
                        <tr class="text-muted">
                            <th>Date</th>
                            <th>Method</th>
                            <th>Transaction ID</th>
                            <th class="text-end">Amount Paid</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->payments as $payment)
                        <tr>
                            <td>{{ $payment->payment_date ? $payment->payment_date->format('d M Y') : 'N/A' }}</td>
                            <td class="text-capitalize">{{ $payment->payment_method }}</td>
                            <td>{{ $payment->transaction_id ?? '-' }}</td>
                            <td class="text-end fw-bold text-success">৳{{ number_format($payment->amount, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <!-- Footer -->
        <div class="text-center mt-5 pt-4 border-top text-muted" style="font-size: 12px;">
            <p class="mb-1">Thank you for your payment.</p>
            <p class="mb-0">This is a system generated receipt and does not require a signature.</p>
        </div>
    </div>

    <!-- Print Button -->
    <button onclick="window.print()" class="btn btn-primary print-btn">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-printer me-2" viewBox="0 0 16 16" style="vertical-align: -2px;">
            <path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"/>
            <path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2H5zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4V3zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2H5zm7 2v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1z"/>
        </svg>
        Print Receipt
    </button>

</body>
</html>
