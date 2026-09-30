@extends('layouts.app', ['title' => 'Collection - EduERP', 'header' => 'Collection'])

@section('content')
    <style>
        .nav-pills .nav-link.active {
            color: #fff !important;
        }
    </style>
    <div class="row mb-3 align-items-center">
        <div class="col">
            <h4 class="mb-1 d-flex align-items-center gap-2">
                <i class="fa-solid fa-file-invoice-dollar text-primary"></i> Fee Collection
            </h4>
            <p class="text-secondary mb-0" style="font-size: 0.85rem;">Select a class and student to manage their fees</p>
        </div>
    </div>

    <div class="row g-3">
        <!-- LEFT PANEL: Student List -->
        <div class="col-lg-4 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden" style="min-height: 500px;">
                <!-- Class & Roll Filter -->
                <div class="card-header bg-transparent p-3 border-bottom">
                    <select id="classSelect" class="form-select form-select-sm border shadow-sm rounded-3 mb-2"
                        style="font-size: 0.85rem;">
                        <option value="">Select Class</option>
                        @foreach ($classes as $c)
                            <option value="{{ $c->id }}" {{ request('class_id') == $c->id ? 'selected' : '' }}>
                                {{ $c->name }}</option>
                        @endforeach
                    </select>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-transparent border-end-0"><i
                                class="fa-solid fa-magnifying-glass text-secondary" style="font-size: 0.75rem;"></i></span>
                        <input type="text" id="rollInput" class="form-control border-start-0"
                            placeholder="Search by roll or name..." style="font-size: 0.85rem;">
                    </div>
                </div>

                <!-- Student List -->
                <div class="card-body p-0" id="studentListContainer" style="max-height: 450px; overflow-y: auto;">
                    <div class="p-4 text-center text-secondary" id="studentPlaceholder">
                        <i class="fa-solid fa-users opacity-25" style="font-size: 2.5rem;"></i>
                        <p class="mt-2 mb-0" style="font-size: 0.85rem;">Select a class to view students</p>
                    </div>
                    <div id="studentList" style="display: none;">
                        <!-- AJAX loaded students -->
                    </div>
                </div>

                <!-- Student Count Footer -->
                <div class="card-footer bg-transparent border-top p-2 text-center" id="studentFooter"
                    style="display: none;">
                    <small class="text-secondary"><i class="fa-solid fa-users me-1"></i> <span id="studentCount">0</span>
                        students</small>
                </div>
            </div>
        </div>

        <!-- RIGHT PANEL: Fee Ledger -->
        <div class="col-lg-8 col-xl-9">
            @if (!$student)
                <!-- Empty State -->
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-5 text-center text-secondary">
                        <div class="mb-3 opacity-25">
                            <i class="fa-solid fa-receipt" style="font-size: 4rem;"></i>
                        </div>
                        <h5 class="fw-bold">No Student Selected</h5>
                        <p class="mb-0" style="font-size: 0.9rem;">Select a student from the left panel to view and
                            collect their fees.</p>
                    </div>
                </div>
            @else
                <!-- Student Info Header -->
                <div class="card border-0 shadow-sm rounded-4 mb-3">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                            style="width: 48px; height: 48px; background: linear-gradient(135deg, rgba(var(--bs-primary-rgb), 0.15), rgba(var(--bs-primary-rgb), 0.05));">
                            @if ($student->photo_path)
                                <img src="{{ asset('storage/' . $student->photo_path) }}" class="rounded-circle"
                                    style="width: 48px; height: 48px; object-fit: cover;">
                            @else
                                <i class="fa-solid fa-user-graduate text-primary"></i>
                            @endif
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-0 fw-bold">{{ $student->user->name }}</h6>
                            <div class="d-flex gap-3 text-secondary" style="font-size: 0.8rem;">
                                <span><i class="fa-solid fa-hashtag me-1"></i>Roll: {{ $student->roll_no }}</span>
                                <span><i
                                        class="fa-solid fa-graduation-cap me-1"></i>{{ $student->schoolClass->name ?? 'N/A' }}</span>
                                <span><i
                                        class="fa-solid fa-layer-group me-1"></i>{{ $student->section->name ?? 'N/A' }}</span>
                            </div>
                        </div>
                        <a href="{{ route('admin.fees.collection') }}" class="btn btn-sm btn-light rounded-circle shadow-sm"
                            title="Clear">
                            <i class="fa-solid fa-xmark text-secondary"></i>
                        </a>
                    </div>
                </div>

                <!-- Summary Cards Row -->
                <div class="row g-2 mb-3">
                    <div class="col-6 col-md-3">
                        <div class="card border-0 shadow-sm rounded-3 h-100">
                            <div class="card-body p-2 px-3">
                                <div class="text-secondary fw-semibold" style="font-size: 0.65rem; letter-spacing: 0.5px;">
                                    <i class="fa-solid fa-file-invoice me-1"></i>BILLED
                                </div>
                                <h5 class="fw-bold mb-0 text-primary mt-1" style="font-size: 1.1rem;">
                                    ৳{{ number_format($totalBilled, 0) }}</h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card border-0 shadow-sm rounded-3 h-100">
                            <div class="card-body p-2 px-3">
                                <div class="text-success fw-semibold" style="font-size: 0.65rem; letter-spacing: 0.5px;"><i
                                        class="fa-regular fa-circle-check me-1"></i>PAID</div>
                                <h5 class="fw-bold mb-0 text-success mt-1" style="font-size: 1.1rem;">
                                    ৳{{ number_format($totalPaid, 0) }}</h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card border-0 shadow-sm rounded-3 h-100">
                            <div class="card-body p-2 px-3">
                                <div class="text-danger fw-semibold" style="font-size: 0.65rem; letter-spacing: 0.5px;"><i
                                        class="fa-solid fa-triangle-exclamation me-1"></i>DUE</div>
                                <h5 class="fw-bold mb-0 text-danger mt-1" style="font-size: 1.1rem;">
                                    ৳{{ number_format($totalDue, 0) }}</h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="card border-0 shadow-sm rounded-3 h-100">
                            <div class="card-body p-2 px-3">
                                <div class="text-info fw-semibold" style="font-size: 0.65rem; letter-spacing: 0.5px;"><i
                                        class="fa-solid fa-wallet me-1"></i>ADVANCE</div>
                                <h5 class="fw-bold mb-0 text-info mt-1" style="font-size: 1.1rem;">
                                    ৳{{ number_format($totalAdvance, 0) }}</h5>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Progress -->
                @php
                    $percentage = $totalBilled > 0 ? round(($totalPaid / $totalBilled) * 100) : 0;

                    // Calculate total unpaid items (including ungenerated ones)
                    $totalUnpaidItems = 0;
                    if ($student && $feeStructures) {
                        foreach ($feeStructures as $structure) {
                            $catItems = $invoices->flatMap->items->where(
                                'fee_category_id',
                                $structure->fee_category_id,
                            );
                            $installmentsCount = $structure->feeCategory->installments_count ?: 1;
                            $installmentAmount = $structure->amount / $installmentsCount;

                            $definedInstallments = $structure->feeCategory->installments;
                            if (!$definedInstallments || $definedInstallments->isEmpty()) {
                                $definedInstallments = collect();
                                for ($i = 1; $i <= $installmentsCount; $i++) {
                                    $name = $installmentsCount > 1 ? 'Installment ' . $i : 'Full Payment';
                                    $definedInstallments->push((object) ['name' => $name]);
                                }
                            }

                            foreach ($definedInstallments as $inst) {
                                $item = $catItems->firstWhere('installment_name', $inst->name);
                                $itemDiscount = $item && $item->invoice ? $item->invoice->discount_amount : 0;
                                $itemPaid = $item ? $item->paid_amount ?? 0 : 0;
                                $itemBilled = round(
                                    max(0, ($item ? $item->amount : $installmentAmount) - $itemDiscount),
                                );
                                $itemDue = round($itemBilled - $itemPaid);

                                if ($itemDue > 0) {
                                    $totalUnpaidItems++;
                                }
                            }
                        }
                    }
                @endphp
                <div class="d-flex align-items-center mb-3 gap-2">
                    <div class="progress flex-grow-1 rounded-pill"
                        style="height: 5px; background-color: rgba(var(--bs-primary-rgb), 0.08);">
                        <div class="progress-bar bg-primary rounded-pill" style="width: {{ $percentage }}%;"></div>
                    </div>
                    <small class="text-secondary fw-semibold" style="font-size: 0.7rem;">{{ $percentage }}%</small>
                </div>

                <!-- Tabs -->
                <ul class="nav nav-pills gap-2 mb-3" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active rounded-pill px-3 py-1 fw-semibold" style="font-size: 0.85rem;"
                            data-bs-toggle="pill" data-bs-target="#collectTab" type="button" role="tab"
                            aria-controls="collectTab" aria-selected="true">
                            <i class="fa-regular fa-credit-card me-1"></i> Collect
                            @if ($totalUnpaidItems > 0)
                                <span class="badge bg-danger rounded-circle ms-1"
                                    style="font-size: 0.55rem;">{{ $totalUnpaidItems }}</span>
                            @endif
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link rounded-pill px-3 py-1 fw-semibold text-secondary"
                            style="font-size: 0.85rem;" data-bs-toggle="pill" data-bs-target="#historyTab"
                            type="button" role="tab" aria-controls="historyTab" aria-selected="false">
                            <i class="fa-solid fa-clock-rotate-left me-1"></i> History
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link rounded-pill px-3 py-1 fw-semibold text-secondary"
                            style="font-size: 0.85rem;" data-bs-toggle="pill" data-bs-target="#refundTab" type="button"
                            role="tab" aria-controls="refundTab" aria-selected="false">
                            <i class="fa-solid fa-rotate-left me-1"></i> Refund
                        </button>
                    </li>
                </ul>

                <div class="tab-content">
                    <!-- Collect Tab -->
                    <div class="tab-pane fade show active" id="collectTab">
                        <div class="accordion accordion-flush" id="ledgerAccordion">
                            @forelse($feeStructures as $structure)
                                @php
                                    $structId = $structure->id ?: \Illuminate\Support\Str::slug($structure->feeCategory->name);
                                    
                                    $catItems = $invoices->flatMap->items->where(
                                        'fee_category_id',
                                        $structure->fee_category_id,
                                    );

                                    $isHostelStructure = str_starts_with($structure->feeCategory->name, 'Hostel Fee');
                                    $isTransportStructure = str_starts_with($structure->feeCategory->name, 'Transport Fee');
                                    $isFoodStructure = str_starts_with($structure->feeCategory->name, 'Food Fee');
                                    $isDynamicStructure = $isHostelStructure || $isTransportStructure || $isFoodStructure;
                                    
                                    $dynamicMonths = [];
                                    if ($isHostelStructure && isset($structure->feeCategory->validHostelMonths)) {
                                        $dynamicMonths = $structure->feeCategory->validHostelMonths;
                                    } elseif ($isTransportStructure && isset($structure->feeCategory->validTransportMonths)) {
                                        $dynamicMonths = $structure->feeCategory->validTransportMonths;
                                    } elseif ($isFoodStructure && isset($structure->feeCategory->validFoodMonths)) {
                                        $dynamicMonths = $structure->feeCategory->validFoodMonths;
                                    }

                                    $catDiscount = $catItems
                                        ->map(function ($i) {
                                            return $i->invoice ? $i->invoice->discount_amount : 0;
                                        })
                                        ->sum();

                                    $installmentsCount = $structure->feeCategory->installments_count ?: 1;
                                    $installmentAmount = $structure->amount / $installmentsCount;

                                    if ($isDynamicStructure && !empty($dynamicMonths)) {
                                        $validMonthsCount = count($dynamicMonths);
                                        $displayBilled = max(0, ($validMonthsCount * $installmentAmount) - $catDiscount);
                                    } else {
                                        $displayBilled = max(0, $structure->amount - $catDiscount);
                                    }
                                    $catPaid = $catItems->sum('paid_amount');
                                    $catDue = max(0, $displayBilled - $catPaid);
                                @endphp
                                <div class="card border-0 shadow-sm rounded-3 mb-2 overflow-hidden">
                                    @php
                                        $installmentsCount = $structure->feeCategory->installments_count ?: 1;
                                        $installmentAmount = $structure->amount / $installmentsCount;
                                        $isMonthly = $installmentsCount == 12;
                                        
                                        $definedInstallments = $structure->feeCategory->installments;
                                        if ($definedInstallments->isEmpty()) {
                                            $definedInstallments = collect();

                                            if ($isDynamicStructure && count($dynamicMonths) > 0) {
                                                $i = 1;
                                                foreach ($dynamicMonths as $ym) {
                                                    $definedInstallments->push(
                                                        (object) [
                                                            'id' => 'dynamic-' . $i++,
                                                            'name' => \Carbon\Carbon::createFromFormat('Y-m', $ym)->format('F Y'),
                                                            'due_date' => null,
                                                            'ym' => $ym,
                                                        ],
                                                    );
                                                }
                                            } else {
                                                if ($installmentsCount == 12) {
                                                    for ($i = 1; $i <= 12; $i++) {
                                                        $definedInstallments->push(
                                                            (object) [
                                                                'id' => $i,
                                                                'name' => date('F', mktime(0, 0, 0, $i, 1)),
                                                                'due_date' => null,
                                                            ],
                                                        );
                                                    }
                                                } else {
                                                    for ($i = 1; $i <= $installmentsCount; $i++) {
                                                        if (
                                                            $structure->feeCategory->type === 'monthly' &&
                                                            $installmentsCount > 1
                                                        ) {
                                                            $name = date('F', mktime(0, 0, 0, $i, 1));
                                                        } else {
                                                            $name =
                                                                $installmentsCount > 1
                                                                    ? 'Installment ' . $i
                                                                    : 'Full Payment';
                                                        }
                                                        $definedInstallments->push(
                                                            (object) [
                                                                'id' => 'mock-' . $i,
                                                                'name' => $name,
                                                                'due_date' => null,
                                                            ],
                                                        );
                                                    }
                                                }
                                            }
                                        }

                                        $dynamicYears = [];
                                        $yearlyTotals = [];
                                        if ($isDynamicStructure) {
                                            foreach ($definedInstallments as $di) {
                                                if (isset($di->ym)) {
                                                    $year = explode('-', $di->ym)[0];
                                                    if (!in_array($year, $dynamicYears)) {
                                                        $dynamicYears[] = $year;
                                                    }
                                                }
                                            }
                                            sort($dynamicYears);
                                            
                                            foreach ($dynamicYears as $hy) {
                                                $yBilled = 0;
                                                $yPaid = 0;
                                                $yDue = 0;
                                                foreach ($definedInstallments as $inst) {
                                                    if (isset($inst->ym) && explode('-', $inst->ym)[0] == $hy) {
                                                        $item = $catItems->firstWhere('installment_name', $inst->name);
                                                        $iDisc = $item && $item->invoice ? $item->invoice->discount_amount : 0;
                                                        $iPaid = $item ? $item->paid_amount ?? 0 : 0;
                                                        $iBilled = round(max(0, ($item ? $item->amount : $installmentAmount) - $iDisc));
                                                        $iDue = round($iBilled - $iPaid);
                                                        
                                                        $yBilled += $iBilled;
                                                        $yPaid += $iPaid;
                                                        $yDue += max(0, $iDue);
                                                    }
                                                }
                                                $yearlyTotals[$hy] = [
                                                    'billed' => number_format($yBilled, 0),
                                                    'paid' => number_format($yPaid, 0),
                                                    'due' => number_format($yDue, 0)
                                                ];
                                            }
                                        }
                                    @endphp
                                    <div class="accordion-item border-0">
                                        <h2 class="accordion-header">
                                            <button
                                                class="accordion-button {{ session()->has('active_structure') && session('active_structure') == $structId ? '' : 'collapsed' }} bg-transparent py-2 px-3"
                                                type="button" data-bs-toggle="collapse"
                                                data-bs-target="#struct-{{ $structId }}">
                                                <div class="row w-100 align-items-center" style="font-size: 0.85rem;">
                                                    <div class="col-4">
                                                        <span class="fw-bold">{{ $structure->feeCategory->name }}</span>
                                                        <span class="text-secondary ms-1"
                                                            style="font-size: 0.7rem;">
                                                            {{ $isHostelStructure && isset($validMonthsCount) ? $validMonthsCount : $installmentsCount }}
                                                            installments
                                                        </span>
                                                        @php
                                                            $latestAlloc = null;
                                                            if ($isHostelStructure) {
                                                                $latestAlloc = $student->hostelAllocations->sortByDesc('id')->first();
                                                            } elseif ($isTransportStructure) {
                                                                $latestAlloc = $student->transportAllocations->sortByDesc('id')->first();
                                                            } elseif ($isFoodStructure) {
                                                                $latestAlloc = $student->foodAllocations->sortByDesc('id')->first();
                                                            }
                                                            $isReleasedNow = $isDynamicStructure && $latestAlloc && in_array($latestAlloc->status, ['released', 'cancelled', 'inactive']);
                                                        @endphp
                                                        @if($isReleasedNow)
                                                            <br><span class="badge bg-danger bg-opacity-10 text-danger border mt-1" style="font-size: 0.65rem;">
                                                                Released on {{ $latestAlloc->updated_at->format('d M, Y') }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                    <div class="col-8 d-flex justify-content-end gap-4 text-center">
                                                        <div>
                                                            <div class="text-secondary" style="font-size: 0.65rem;">
                                                                {{ $isMonthly ? 'YEARLY BILLED' : 'BILLED' }}
                                                            </div>
                                                            <div class="fw-bold" id="billed-amount-{{ $structId }}">
                                                                ৳{{ number_format($displayBilled, 0) }}
                                                                @if ($isMonthly)
                                                                    <span class="text-muted fw-normal"
                                                                        style="font-size: 0.65rem;">(৳{{ number_format($installmentAmount, 0) }}/mo)</span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <div class="text-secondary" style="font-size: 0.65rem;">TOTAL
                                                                PAID
                                                            </div>
                                                            <div class="fw-bold text-success" id="paid-amount-{{ $structId }}">
                                                                ৳{{ number_format($catPaid, 0) }}</div>
                                                        </div>
                                                        <div>
                                                            <div class="text-secondary" style="font-size: 0.65rem;">TOTAL
                                                                DUE
                                                            </div>
                                                            <div class="fw-bold text-danger" id="due-amount-{{ $structId }}">
                                                                ৳{{ number_format(max(0, $catDue), 0) }}</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </button>
                                        </h2>
                                        <div id="struct-{{ $structId }}"
                                            class="accordion-collapse collapse {{ session()->has('active_structure') && session('active_structure') == $structId ? 'show' : '' }}"
                                            data-bs-parent="#ledgerAccordion">
                                            <div class="accordion-body p-0">
                                                    @if ($isDynamicStructure && count($dynamicYears) >= 1)
                                                        <div class="d-flex justify-content-between align-items-center mb-2 px-3 pt-3">
                                                            <div class="fw-semibold text-secondary" style="font-size: 0.75rem;">SELECT YEAR</div>
                                                            <select class="form-select form-select-sm w-auto hostel-year-select" 
                                                                    data-totals="{{ json_encode($yearlyTotals) }}"
                                                                    data-monthly="{{ number_format($installmentAmount, 0) }}"
                                                                    onchange="filterHostelYear(this, '{{ $structId }}')">
                                                                @foreach($dynamicYears as $hy)
                                                                    <option value="{{ $hy }}">{{ $hy }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    @endif

                                                @foreach ($definedInstallments as $inst)
                                                    @php
                                                        $item = $catItems->firstWhere('installment_name', $inst->name);
                                                        $itemDiscount =
                                                            $item && $item->invoice
                                                                ? $item->invoice->discount_amount
                                                                : 0;
                                                        $itemPaid = $item ? $item->paid_amount ?? 0 : 0;
                                                        $itemBilled = round(
                                                            max(
                                                                0,
                                                                ($item ? $item->amount : $installmentAmount) -
                                                                    $itemDiscount,
                                                            ),
                                                        );
                                                        $itemDue = round($itemBilled - $itemPaid);
                                                        $isItemPaid = $item ? $itemDue <= 0 : false;

                                                        $isDynamicActive = false;
                                                        if ($isHostelStructure) {
                                                            $isDynamicActive = $student->hostelAllocation && $student->hostelAllocation->status === 'active';
                                                        } elseif ($isTransportStructure) {
                                                            $isDynamicActive = $student->transportAllocation && $student->transportAllocation->status === 'active';
                                                        } elseif ($isFoodStructure) {
                                                            $isDynamicActive = $student->is_food_enabled && $student->foodAllocation && $student->foodAllocation->status === 'active';
                                                        }

                                                        $canGenerateNew = !$isDynamicStructure || $isDynamicActive;

                                                        $isValidMonth = true;
                                                        if ($isDynamicStructure) {
                                                            $validMonths = $dynamicMonths ?? range(1, 12);

                                                            $instYm = isset($inst->ym)
                                                                ? $inst->ym
                                                                : date('Y-m', strtotime($inst->name));

                                                            if (!in_array($instYm, $validMonths)) {
                                                                $isValidMonth = false;
                                                            }
                                                        }

                                                        $paymentDateStr = '';
                                                        if ($isItemPaid && $item && $item->invoice) {
                                                            $lastPayment = $item->invoice->payments->last();
                                                            if ($lastPayment && $lastPayment->payment_date) {
                                                                $paymentDateStr =
                                                                    \Carbon\Carbon::parse(
                                                                        $lastPayment->payment_date,
                                                                    )->format('d/m/Y') . ' ';
                                                            }
                                                        }
                                                    @endphp
                                                    @if ($isDynamicStructure)
                                                        @if (!$isValidMonth && !$item)
                                                            @continue
                                                        @endif
                                                        @if (!$isDynamicActive)
                                                            @if ($isItemPaid || (!$isValidMonth && !$item))
                                                                @continue
                                                            @endif
                                                        @endif
                                                    @endif
                                                    @php
                                                        $instYear = '';
                                                        if ($isDynamicStructure && isset($inst->ym)) {
                                                            $instYear = explode('-', $inst->ym)[0];
                                                        }
                                                    @endphp
                                                    <div class="accordion accordion-flush {{ $isDynamicStructure ? 'hostel-inst-' . $structId : '' }}"
                                                        id="itemAccordion-{{ $structId }}-{{ $inst->id }}"
                                                        @if($isDynamicStructure) data-year="{{ $instYear }}" @endif>
                                                        <div class="accordion-item border-0 border-top">
                                                            <h2 class="accordion-header">
                                                                <button
                                                                    class="accordion-button collapsed py-2 px-3 {{ $isItemPaid ? 'bg-opacity-10' : '' }}"
                                                                    type="button" data-bs-toggle="collapse"
                                                                    data-bs-target="#item-collapse-{{ $structId }}-{{ $inst->id }}">
                                                                    <div class="d-flex justify-content-between w-100 align-items-center"
                                                                        style="font-size: 0.85rem;">
                                                                        <div class="fw-semibold">
                                                                            {{ $inst->name }}
                                                                            <div class="text-secondary mt-1"
                                                                                style="font-size: 0.7rem;">
                                                                                @if ($inst->due_date)
                                                                                    <i
                                                                                        class="fa-regular fa-calendar me-1"></i>Due:
                                                                                    {{ \Carbon\Carbon::parse($inst->due_date)->format('M d, Y') }}
                                                                                @endif
                                                                                @if ($isItemPaid)
                                                                                    <span
                                                                                        class="badge bg-success bg-opacity-10 text-success {{ $inst->due_date ? 'ms-2' : '' }}"
                                                                                        style="font-size: 0.6rem;">{{ $paymentDateStr }}PAID</span>
                                                                                @else
                                                                                    <span
                                                                                        class="badge bg-warning bg-opacity-10 text-warning {{ $inst->due_date ? 'ms-2' : '' }}"
                                                                                        style="font-size: 0.6rem;">PENDING</span>
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                        <div class="d-flex gap-4 text-center">
                                                                            <div>
                                                                                <div class="text-secondary"
                                                                                    style="font-size: 0.65rem;">AMOUNT
                                                                                </div>
                                                                                <div class="fw-medium">
                                                                                    ৳{{ number_format($itemBilled, 0) }}
                                                                                </div>
                                                                            </div>
                                                                            <div>
                                                                                <div class="text-secondary"
                                                                                    style="font-size: 0.65rem;">PAID</div>
                                                                                <div class="text-success fw-medium">
                                                                                    ৳{{ number_format($itemPaid, 0) }}
                                                                                </div>
                                                                            </div>
                                                                            <div>
                                                                                <div class="text-secondary"
                                                                                    style="font-size: 0.65rem;">DUE</div>
                                                                                <div class="text-danger fw-medium">
                                                                                    ৳{{ number_format(max(0, $itemDue), 0) }}
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </button>
                                                            </h2>
                                                            <div id="item-collapse-{{ $structId }}-{{ $inst->id }}"
                                                                class="accordion-collapse collapse"
                                                                data-bs-parent="#itemAccordion-{{ $structId }}-{{ $inst->id }}">
                                                                <div class="accordion-body bg-secondary bg-opacity-10 p-3">
                                                                    @if (!$isItemPaid)
                                                                        <form
                                                                            action="{{ $item ? '/admin/fees/invoices/' . $item->invoice_id . '/pay' : route('admin.fees.direct_collect') }}"
                                                                            method="POST">
                                                                            @csrf
                                                                            <input type="hidden" name="structure_id"
                                                                                value="{{ $structId }}">
                                                                            @if (!$item)
                                                                                <input type="hidden" name="student_id"
                                                                                    value="{{ $student->id }}">
                                                                                <input type="hidden"
                                                                                    name="fee_category_id"
                                                                                    value="{{ $structure->fee_category_id }}">
                                                                                <input type="hidden"
                                                                                    name="installment_name"
                                                                                    value="{{ $inst->name }}">
                                                                                <input type="hidden" name="base_amount"
                                                                                    value="{{ round($itemBilled) }}">
                                                                            @endif
                                                                            <div class="row g-2 align-items-end">
                                                                                <div class="col-md-2">
                                                                                    <label
                                                                                        class="form-label fw-semibold text-secondary mb-1"
                                                                                        style="font-size: 0.7rem;">AMOUNT</label>
                                                                                    <input type="number" name="amount"
                                                                                        class="form-control form-control-sm border shadow-sm amount-input"
                                                                                        value="{{ max(0, $itemDue) }}"
                                                                                        data-base-amount="{{ max(0, $itemDue) }}"
                                                                                        required>
                                                                                </div>
                                                                                <div class="col-md-3">
                                                                                    <label
                                                                                        class="form-label fw-semibold text-secondary mb-1"
                                                                                        style="font-size: 0.7rem;">DISCOUNT</label>
                                                                                    <div
                                                                                        class="input-group input-group-sm shadow-sm">
                                                                                        <select name="discount_type"
                                                                                            class="form-select border discount-type"
                                                                                            style="max-width: 60px;">
                                                                                            <option value="amount">৳
                                                                                            </option>
                                                                                            <option value="percent">%
                                                                                            </option>
                                                                                        </select>
                                                                                        <input type="number"
                                                                                            name="discount"
                                                                                            class="form-control border discount-input"
                                                                                            value="0" min="0"
                                                                                            step="0.01">
                                                                                    </div>
                                                                                </div>
                                                                                <div class="col-md-2">
                                                                                    <label
                                                                                        class="form-label fw-semibold text-secondary mb-1"
                                                                                        style="font-size: 0.7rem;">DATE</label>
                                                                                    <input type="date"
                                                                                        name="payment_date"
                                                                                        class="form-control form-control-sm border shadow-sm"
                                                                                        value="{{ date('Y-m-d') }}"
                                                                                        required>
                                                                                </div>
                                                                                <div class="col-md-2">
                                                                                    <label
                                                                                        class="form-label fw-semibold text-secondary mb-1"
                                                                                        style="font-size: 0.7rem;">METHOD</label>
                                                                                    <select name="payment_method"
                                                                                        class="form-select form-select-sm border shadow-sm"
                                                                                        required>
                                                                                        <option value="cash">Cash
                                                                                        </option>
                                                                                        <option value="bkash">bKash
                                                                                        </option>
                                                                                        <option value="nagad">Nagad
                                                                                        </option>
                                                                                        <option value="bank">Bank
                                                                                            Transfer</option>
                                                                                    </select>
                                                                                </div>
                                                                                <div class="col-md-2">
                                                                                    <label
                                                                                        class="form-label fw-semibold text-secondary mb-1"
                                                                                        style="font-size: 0.7rem;">NOTE</label>
                                                                                    <input type="text"
                                                                                        name="transaction_id"
                                                                                        class="form-control form-control-sm border shadow-sm"
                                                                                        placeholder="Optional note">
                                                                                </div>
                                                                                <div class="col-md-1">
                                                                                    <button type="submit"
                                                                                        class="btn btn-sm btn-dark w-100 rounded-2 shadow-sm d-flex align-items-center justify-content-center h-100 py-2"><i
                                                                                            class="fa-regular fa-circle-check me-1"></i>
                                                                                        Pay</button>
                                                                                </div>
                                                                            </div>
                                                                        </form>
                                                                    @endif

                                                                    @if ($item && $item->invoice && $item->invoice->payments && $item->invoice->payments->count() > 0)
                                                                        <div
                                                                            class="mt-3 {{ !$isItemPaid ? 'border-top pt-3 mt-3' : '' }}">
                                                                            <div class="table-responsive">
                                                                                <table
                                                                                    class="table table-sm table-borderless mb-0"
                                                                                    style="font-size: 0.75rem;">
                                                                                    <thead
                                                                                        class="border-bottom border-success border-opacity-25 text-success">
                                                                                        <tr>
                                                                                            <th>Receipt #</th>
                                                                                            <th>Date</th>
                                                                                            <th>Method</th>
                                                                                            <th>DISCOUNT</th>
                                                                                            <th>REFUND</th>
                                                                                            <th class="text-end">AMOUNT
                                                                                                PAID</th>
                                                                                            @if (auth()->user()->hasRole('super_admin'))
                                                                                                <th class="text-center"
                                                                                                    style="width: 50px;">
                                                                                                    ACTION</th>
                                                                                            @endif
                                                                                        </tr>
                                                                                    </thead>
                                                                                    <tbody>
                                                                                        @if ($item && $item->invoice && $item->invoice->payments)
                                                                                            @php
                                                                                                $totalPayments = $item->invoice->payments->sum(
                                                                                                    'amount',
                                                                                                );
                                                                                                $refundedAmount = max(
                                                                                                    0,
                                                                                                    $totalPayments -
                                                                                                        $item->invoice
                                                                                                            ->paid_amount,
                                                                                                );
                                                                                            @endphp
                                                                                            @foreach ($item->invoice->payments as $payment)
                                                                                                <tr>
                                                                                                    <td class="fw-medium">
                                                                                                        {{ $payment->payment_number }}
                                                                                                    </td>
                                                                                                    <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') }}
                                                                                                    </td>
                                                                                                    <td><span
                                                                                                            class="badge bg-secondary bg-opacity-10 text-secondary border">{{ ucfirst($payment->payment_method) }}</span>
                                                                                                    </td>
                                                                                                    <td>
                                                                                                        @if ($loop->first && $item->invoice->discount_amount > 0)
                                                                                                            <span
                                                                                                                class="text-success">৳{{ number_format($item->invoice->discount_amount, 0) }}</span>
                                                                                                        @else
                                                                                                            -
                                                                                                        @endif
                                                                                                    </td>
                                                                                                    <td>
                                                                                                        @if ($loop->first)
                                                                                                            @php
                                                                                                                $advanceAmount = max(
                                                                                                                    0,
                                                                                                                    $item
                                                                                                                        ->invoice
                                                                                                                        ->paid_amount -
                                                                                                                        $item
                                                                                                                            ->invoice
                                                                                                                            ->grand_total,
                                                                                                                );
                                                                                                            @endphp
                                                                                                            @if ($advanceAmount > 0 || $refundedAmount > 0)
                                                                                                                <div
                                                                                                                    class="d-flex flex-column gap-1 align-items-start">
                                                                                                                    @if ($advanceAmount > 0)
                                                                                                                        <span
                                                                                                                            class="badge bg-info bg-opacity-10 text-info border">৳{{ number_format($advanceAmount, 0) }}
                                                                                                                            Refundable</span>
                                                                                                                    @endif
                                                                                                                    @if ($refundedAmount > 0)
                                                                                                                        <span
                                                                                                                            class="badge bg-danger bg-opacity-10 text-danger border">৳{{ number_format($refundedAmount, 0) }}
                                                                                                                            Refunded</span>
                                                                                                                    @endif
                                                                                                                </div>
                                                                                                            @else
                                                                                                                -
                                                                                                            @endif
                                                                                                        @else
                                                                                                            -
                                                                                                        @endif
                                                                                                    </td>
                                                                                                    <td
                                                                                                        class="text-end fw-bold text-success">
                                                                                                        ৳{{ number_format($payment->amount, 0) }}
                                                                                                    </td>
                                                                                                    @if (auth()->user()->hasRole('super_admin'))
                                                                                                        <td
                                                                                                            class="text-center">
                                                                                                            <form
                                                                                                                id="delete-payment-{{ $payment->id }}"
                                                                                                                action="{{ route('admin.fees.payments.destroy', $payment->id) }}"
                                                                                                                method="POST"
                                                                                                                class="d-inline">
                                                                                                                @csrf
                                                                                                                @method('DELETE')
                                                                                                                <button
                                                                                                                    type="button"
                                                                                                                    class="btn btn-sm btn-outline-danger py-0 px-1 border-0"
                                                                                                                    onclick="deletePayment(event, 'delete-payment-{{ $payment->id }}')"
                                                                                                                    title="Delete Payment">
                                                                                                                    <i
                                                                                                                        class="fa-solid fa-trash-can"></i>
                                                                                                                </button>
                                                                                                            </form>
                                                                                                        </td>
                                                                                                    @endif
                                                                                                </tr>
                                                                                            @endforeach
                                                                                        @endif

                                                                                    </tbody>
                                                                                </table>
                                                                            </div>
                                                                            <div class="text-success fw-bold text-center mt-2"
                                                                                style="font-size: 0.85rem;">
                                                                                <i
                                                                                    class="fa-solid fa-circle-check me-1"></i>
                                                                                This item is fully paid.
                                                                            </div>
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                                @if (!$canGenerateNew)
                                                    <div class="text-center text-muted py-3 border-top"
                                                        style="font-size: 0.85rem;">
                                                        <i class="fa-solid fa-ban fs-4 mb-2 d-block opacity-50"></i>
                                                        Student is released from the {{ $isHostelStructure ? 'hostel' : ($isTransportStructure ? 'transport service' : 'food service') }}. You cannot generate new bills
                                                        for this month.
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="card border-0 shadow-sm rounded-3">
                                    <div class="card-body p-4 text-center text-secondary">
                                        <i class="fa-regular fa-folder-open fs-2 mb-2 opacity-50"></i>
                                        <h6>No Fee Setup Found</h6>
                                        <p class="mb-0" style="font-size: 0.85rem;">Configure fee structures for this
                                            class first.
                                        </p>
                                    </div>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- History Tab -->
                    <div class="tab-pane fade" id="historyTab" role="tabpanel">
                        <div class="card border-0 shadow-sm rounded-3">
                            <div class="card-body p-3">
                                @if ($invoices && $invoices->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-hover table-sm mb-0" style="font-size: 0.85rem;">
                                            <thead>
                                                <tr class="text-secondary" style="font-size: 0.75rem;">
                                                    <th>Invoice #</th>
                                                    <th>Fee Details</th>
                                                    <th>Issue Date</th>
                                                    <th class="text-end">Billed</th>
                                                    <th class="text-end">Paid</th>
                                                    <th class="text-center">Status</th>
                                                    <th class="text-center">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($invoices as $inv)
                                                    <tr>
                                                        <td class="fw-bold">{{ $inv->invoice_number }}</td>
                                                        <td>
                                                            @foreach($inv->items as $item)
                                                                <div class="mb-1">
                                                                    <span class="fw-semibold">{{ $item->feeCategory->name ?? 'Unknown Fee' }}</span>
                                                                    <span class="text-muted" style="font-size: 0.75rem;">({{ $item->installment_name }})</span>
                                                                </div>
                                                            @endforeach
                                                        </td>
                                                        <td>{{ $inv->issue_date ? $inv->issue_date->format('d M Y') : 'N/A' }}
                                                        </td>
                                                        <td class="text-end fw-medium">
                                                            ৳{{ number_format($inv->grand_total, 0) }}</td>
                                                        <td class="text-end text-success fw-medium">
                                                            ৳{{ number_format($inv->paid_amount, 0) }}</td>
                                                        <td class="text-center align-middle">
                                                            @if ($inv->grand_total - $inv->paid_amount <= 0)
                                                                <span
                                                                    class="badge bg-success bg-opacity-10 text-success rounded-pill px-2"
                                                                    style="font-size: 0.7rem;">Paid</span>
                                                            @else
                                                                <span
                                                                    class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-2"
                                                                    style="font-size: 0.7rem;">Due</span>
                                                            @endif
                                                        </td>
                                                        <td class="text-center align-middle">
                                                            <a href="{{ route('admin.fees.invoices.receipt', $inv->id) }}" target="_blank" class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size: 0.75rem;">
                                                                <i class="fa-solid fa-print"></i> Receipt
                                                            </a>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="text-center text-secondary py-3">
                                        <i class="fa-regular fa-clock fs-2 mb-2 opacity-50"></i>
                                        <h6>No Payment History</h6>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <!-- Refund Tab -->
                    <div class="tab-pane fade" id="refundTab" role="tabpanel">
                        <div class="card border-0 shadow-sm rounded-3">
                            <div class="card-body p-3">
                                @php
                                    $advances = $invoices
                                        ? $invoices->filter(function ($inv) {
                                            return $inv->paid_amount > $inv->grand_total;
                                        })
                                        : collect();
                                @endphp
                                @if ($advances->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-hover table-sm mb-0" style="font-size: 0.85rem;">
                                            <thead>
                                                <tr class="text-secondary" style="font-size: 0.75rem;">
                                                    <th>Invoice #</th>
                                                    <th>Billed</th>
                                                    <th>Paid</th>
                                                    <th class="text-end">Advance Amount</th>
                                                    <th class="text-center">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($advances as $inv)
                                                    <tr>
                                                        <td class="fw-bold">{{ $inv->invoice_number }}</td>
                                                        <td>৳{{ number_format($inv->grand_total, 0) }}</td>
                                                        <td class="text-success">
                                                            ৳{{ number_format($inv->paid_amount, 0) }}
                                                        </td>
                                                        <td class="text-end fw-bold text-info">
                                                            ৳{{ number_format($inv->paid_amount - $inv->grand_total, 0) }}
                                                        </td>
                                                        <td class="text-center">
                                                            <form
                                                                action="{{ route('admin.fees.advance.refund', $inv->id) }}"
                                                                method="POST"
                                                                onsubmit="return confirm('Are you sure you want to refund this advance amount?');">
                                                                @csrf
                                                                <button type="submit"
                                                                    class="btn btn-sm btn-outline-info py-0 px-2"
                                                                    style="font-size: 0.7rem;"><i
                                                                        class="fa-solid fa-rotate-left"></i> Refund
                                                                    Advance</button>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="text-center text-secondary py-3">
                                        <i class="fa-solid fa-rotate-left fs-2 mb-2 opacity-50"></i>
                                        <h6>No Advance Amounts to Refund</h6>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Collect Payment Modal -->
    <div class="modal fade" id="collectPaymentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-bottom px-4 py-3">
                    <h6 class="modal-title fw-bold">Collect Payment</h6>
                    <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="modal"></button>
                </div>
                <form action="#" method="POST" id="collectPaymentForm">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="card border rounded-3 mb-3">
                            <div class="card-body p-3">
                                <h6 class="fw-bold mb-1" id="modalFeeName">Fee</h6>
                                <div class="d-flex justify-content-between">
                                    <span class="text-secondary" style="font-size: 0.85rem;">Amount Due:</span>
                                    <span class="fw-bold text-danger" id="modalDueAmount">৳0</span>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-secondary" style="font-size: 0.85rem;">Received
                                Amount (৳) <span class="text-danger">*</span></label>
                            <input type="number" name="amount" id="modalAmountInput"
                                class="form-control form-control-lg fw-bold text-success border shadow-sm" value="0"
                                min="1" required>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-secondary" style="font-size: 0.85rem;">Payment
                                    Method</label>
                                <select name="payment_method" class="form-select border shadow-sm" required>
                                    <option value="cash">Cash</option>
                                    <option value="bkash">bKash</option>
                                    <option value="nagad">Nagad</option>
                                    <option value="bank">Bank Transfer</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-secondary"
                                    style="font-size: 0.85rem;">Date</label>
                                <input type="date" name="payment_date" class="form-control border shadow-sm"
                                    value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top px-4 py-3">
                        <button type="button" class="btn btn-light rounded-pill px-4"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success rounded-pill px-4 shadow-sm"><i
                                class="fa-solid fa-check me-1"></i> Confirm</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .accordion-button:not(.collapsed) {
            background-color: transparent;
            color: inherit;
            box-shadow: none;
        }

        .accordion-button:focus {
            box-shadow: none;
        }

        .accordion-button::after {
            width: 0.8rem;
            height: 0.8rem;
            background-size: 0.8rem;
        }

        /* Enforce global border color */
        .border,
        .border-bottom,
        .border-top,
        .form-control,
        .form-select,
        .input-group-text,
        .accordion-item,
        .card,
        .nav-tabs {
            border-color: var(--bs-border-color) !important;
        }

        /* Student item in side panel */
        .student-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            text-decoration: none;
            color: inherit;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            transition: all 0.15s ease;
            font-size: 0.85rem;
        }

        .student-item:hover {
            background: rgba(var(--bs-primary-rgb), 0.06);
            color: var(--bs-primary);
        }

        .student-item.active-student {
            background: rgba(var(--bs-primary-rgb), 0.1);
            color: var(--bs-primary);
            border-left: 3px solid var(--bs-primary);
        }

        .student-item .s-avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(var(--bs-primary-rgb), 0.15), rgba(var(--bs-primary-rgb), 0.05));
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.75rem;
            color: var(--bs-primary);
            flex-shrink: 0;
        }

        /* Scrollbar */
        #studentListContainer::-webkit-scrollbar {
            width: 4px;
        }

        #studentListContainer::-webkit-scrollbar-thumb {
            background: rgba(0, 0, 0, 0.15);
            border-radius: 2px;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function deletePayment(e, formId) {
            e.preventDefault();
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this! The due amount will increase and ledgers will be updated.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById(formId).submit();
                }
            })
        }

        document.addEventListener('DOMContentLoaded', function() {
            const classSelect = document.getElementById('classSelect');
            const rollInput = document.getElementById('rollInput');
            const studentList = document.getElementById('studentList');
            const studentPlaceholder = document.getElementById('studentPlaceholder');
            const studentCount = document.getElementById('studentCount');
            const studentFooter = document.getElementById('studentFooter');
            const currentStudentId = '{{ request('student_id') }}';

            let debounceTimer;

            function fetchStudents() {
                const classId = classSelect.value;
                const search = rollInput.value.trim();

                if (!classId) {
                    studentList.style.display = 'none';
                    studentPlaceholder.style.display = 'block';
                    studentFooter.style.display = 'none';
                    return;
                }

                let url = '{{ route('admin.fees.search-students') }}?class_id=' + classId;
                if (search) url += '&roll_no=' + search;

                fetch(url)
                    .then(r => r.json())
                    .then(data => {
                        studentPlaceholder.style.display = 'none';
                        studentList.style.display = 'block';
                        studentFooter.style.display = 'block';
                        studentList.innerHTML = '';
                        studentCount.textContent = data.length;

                        if (data.length === 0) {
                            studentList.innerHTML =
                                '<div class="p-4 text-center text-secondary"><i class="fa-regular fa-face-frown fs-3 mb-2 opacity-50"></i><p class="mb-0" style="font-size:0.85rem;">No students found</p></div>';
                            return;
                        }

                        data.forEach(s => {
                            const initials = s.name.split(' ').map(n => n[0]).join('').substring(0, 2)
                                .toUpperCase();
                            const isActive = String(s.id) === String(currentStudentId);
                            const a = document.createElement('a');
                            a.href = '{{ route('admin.fees.collection') }}?student_id=' + s.id +
                                '&class_id=' + classId;
                            a.className = 'student-item' + (isActive ? ' active-student' : '');
                            a.innerHTML = `
                        <div class="s-avatar">${initials}</div>
                        <div class="flex-grow-1">
                            <div class="fw-semibold">${s.name}</div>
                            <div class="text-secondary" style="font-size:0.7rem;">Roll: ${s.roll_no || 'N/A'}</div>
                        </div>
                        <i class="fa-solid fa-chevron-right text-secondary" style="font-size:0.6rem;"></i>
                    `;
                            studentList.appendChild(a);
                        });
                    });
            }

            classSelect.addEventListener('change', function() {
                rollInput.value = '';
                fetchStudents();
            });

            rollInput.addEventListener('input', function() {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(fetchStudents, 300);
            });

            // Auto-load if class is selected
            if (classSelect.value) fetchStudents();

            // Collect Payment Modal
            const modal = document.getElementById('collectPaymentModal');
            if (modal) {
                modal.addEventListener('show.bs.modal', function(e) {
                    const btn = e.relatedTarget;
                    document.getElementById('modalFeeName').textContent = btn.getAttribute(
                        'data-item-name');
                    document.getElementById('modalDueAmount').textContent = '৳' + parseFloat(btn
                        .getAttribute('data-amount')).toLocaleString();
                    document.getElementById('modalAmountInput').value = Math.round(btn.getAttribute(
                        'data-amount'));
                    document.getElementById('collectPaymentForm').action = '/admin/fees/invoices/' + btn
                        .getAttribute('data-invoice-id') + '/pay';
                });
            }

            // Discount Calculation
            document.querySelectorAll('.discount-input, .discount-type').forEach(el => {
                el.addEventListener('input', function() {
                    const row = this.closest('form');
                    const baseAmount = parseFloat(row.querySelector('.amount-input').getAttribute(
                        'data-base-amount')) || 0;
                    const discountType = row.querySelector('.discount-type').value;
                    let discountVal = parseFloat(row.querySelector('.discount-input').value) || 0;

                    let finalAmount = baseAmount;
                    if (discountType === 'percent') {
                        finalAmount -= (baseAmount * discountVal / 100);
                    } else {
                        finalAmount -= discountVal;
                    }

                    row.querySelector('.amount-input').value = Math.max(0, Math.round(finalAmount));
                });
            });

            // Handle Active Tab from Session
            @if (session('active_tab'))
                const targetTab = document.querySelector('[data-bs-target="#{{ session('active_tab') }}"]');
                if (targetTab) {
                    new bootstrap.Tab(targetTab).show();
                }
            @endif

            // Trigger year dropdown to hide other years on load
            document.querySelectorAll('.hostel-year-select').forEach(select => {
                select.dispatchEvent(new Event('change'));
            });
        });

        function filterHostelYear(selectEl, structureId) {
            const year = selectEl.value;
            document.querySelectorAll('.hostel-inst-' + structureId).forEach(el => {
                if (el.getAttribute('data-year') === year) {
                    el.classList.remove('d-none');
                } else {
                    el.classList.add('d-none');
                }
            });
            
            try {
                const totals = JSON.parse(selectEl.getAttribute('data-totals') || '{}');
                const monthly = selectEl.getAttribute('data-monthly') || '0';
                if (totals[year]) {
                    const billedEl = document.getElementById('billed-amount-' + structureId);
                    const paidEl = document.getElementById('paid-amount-' + structureId);
                    const dueEl = document.getElementById('due-amount-' + structureId);
                    
                    if (billedEl) {
                        billedEl.innerHTML = '৳' + totals[year].billed + ' <span class="text-muted fw-normal" style="font-size: 0.65rem;">(৳' + monthly + '/mo)</span>';
                    }
                    if (paidEl) paidEl.innerHTML = '৳' + totals[year].paid;
                    if (dueEl) dueEl.innerHTML = '৳' + totals[year].due;
                }
            } catch (e) {
                console.error('Failed to parse totals', e);
            }
        }

        function deletePayment(e, formId) {
            e.preventDefault();
            Swal.fire({
                title: 'Are you sure?',
                text: "The payment will be deleted and the amount will become pending again.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById(formId).submit();
                }
            });
        }
    </script>
@endpush
