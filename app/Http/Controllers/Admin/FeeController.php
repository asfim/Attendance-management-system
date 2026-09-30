<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\FeeInvoiceRequest;
use App\Services\FeeService;
use App\Models\SchoolClass;
use App\Models\FeeCategory;
use App\Models\FeeStructure;
use App\Models\Invoice;
use App\Models\StudentProfile;
use Illuminate\Http\Request;

class FeeController extends Controller
{
    protected FeeService $feeService;

    public function __construct(FeeService $feeService)
    {
        $this->feeService = $feeService;
    }

    public function setup()
    {
        $classes = SchoolClass::all();
        $categories = FeeCategory::whereNotIn('name', ['Hostel Fee', 'Transport Fee', 'Food Fee'])->get();
        $structures = FeeStructure::with(['feeCategory', 'schoolClass'])
            ->whereHas('feeCategory', function ($q) {
                $q->whereNotIn('name', ['Hostel Fee', 'Transport Fee', 'Food Fee']);
            })->get();
        return view('admin.fees.setup', compact('classes', 'categories', 'structures'));
    }

    public function collection(Request $request)
    {
        $classes = SchoolClass::all();
        $student = null;
        $invoices = collect();
        $totalBilled = 0;
        $totalPaid = 0;
        $totalDue = 0;
        $totalAdvance = 0;
        $feeStructures = collect();

        if ($request->has('student_id') && $request->student_id) {
            $student = StudentProfile::with(['user', 'schoolClass', 'section', 'invoices.items.feeCategory', 'invoices.payments', 'hostelAllocation.bed.room', 'hostelAllocations', 'transportAllocation', 'transportAllocations'])
                ->findOrFail($request->student_id);

            $feeStructures = FeeStructure::with('feeCategory')
                ->where('class_id', $student->class_id)
                ->whereHas('feeCategory', function ($q) {
                    $q->whereNotIn('name', ['Hostel Fee', 'Transport Fee', 'Food Fee']);
                })
                ->get();

            // Dynamic Hostel Fee Injection
            $hostelCategory = FeeCategory::where('name', 'Hostel Fee')->first();
            $hasHostelDue = false;
            $lastHostelItemAmount = 0;

            if ($hostelCategory) {
                $hostelItems = $student->invoices->flatMap->items->where('fee_category_id', $hostelCategory->id);

                $lastHostelItem = $hostelItems->last();
                if ($lastHostelItem) {
                    $lastHostelItemAmount = $lastHostelItem->amount;
                }

                $totalHostelBilled = $hostelItems->sum('amount');
                $totalHostelPaid = $hostelItems->sum('paid_amount');
                $totalHostelDiscount = $hostelItems->map(function ($i) {
                    return $i->invoice ? $i->invoice->discount_amount : 0;
                })->sum();

                $hasHostelDue = ($totalHostelBilled - $totalHostelDiscount - $totalHostelPaid) > 0;

                $allowedEnd = now()->endOfYear()->startOfMonth();
                $maxBilledTimestamp = $hostelItems->max(function($item) {
                    return strtotime($item->installment_name);
                });
                if ($maxBilledTimestamp) {
                    $maxBilledDate = \Carbon\Carbon::createFromTimestamp($maxBilledTimestamp)->startOfMonth();
                    if ($maxBilledDate->year >= $allowedEnd->year && $maxBilledDate->month == 12) {
                         $allowedEnd = $maxBilledDate->copy()->addYear()->endOfYear()->startOfMonth();
                    } elseif ($maxBilledDate->year > $allowedEnd->year) {
                         $allowedEnd = $maxBilledDate->copy()->endOfYear()->startOfMonth();
                    }
                }

                $validHostelMonths = [];
                if ($student->hostelAllocations->isNotEmpty()) {
                    foreach ($student->hostelAllocations as $alloc) {
                        $start = $alloc->allocation_date ? $alloc->allocation_date->copy()->startOfMonth() : now()->startOfMonth();
                        $end = $alloc->status === 'released' ? $alloc->updated_at->copy()->startOfMonth() : $allowedEnd->copy();

                        $current = $start->copy();
                        while ($current <= $end) {
                            $validHostelMonths[] = $current->format('Y-m');
                            $current->addMonth();
                        }
                    }
                } elseif (isset($hasHostelHistory) && $hasHostelHistory) {
                    $firstItem = $hostelItems->sortBy(function($item) {
                        return strtotime($item->installment_name);
                    })->first();
                    if ($firstItem) {
                        $parsed = date_parse($firstItem->installment_name);
                        $year = $parsed['year'] ? $parsed['year'] : date('Y');
                        $start = \Carbon\Carbon::createFromDate($year, $parsed['month'], 1)->startOfMonth();

                        $current = $start->copy();
                        while ($current <= $allowedEnd) {
                            $validHostelMonths[] = $current->format('Y-m');
                            $current->addMonth();
                        }
                    }
                }
                $validHostelMonths = array_unique($validHostelMonths);
                sort($validHostelMonths);

                $billedMonthNumbers = [];
                $paidMonthNumbers = [];
                foreach ($hostelItems as $item) {
                    $parsed = date_parse($item->installment_name);
                    $year = $parsed['year'] ? $parsed['year'] : date('Y');
                    $ym = sprintf('%04d-%02d', $year, $parsed['month']);
                    $billedMonthNumbers[] = $ym;
                    
                    if ($item->paid_amount > 0) {
                        $paidMonthNumbers[] = $ym;
                    }
                }
                
                // Keep paid months in validHostelMonths even if allocation date changed
                $validHostelMonths = array_unique(array_merge($validHostelMonths, $paidMonthNumbers));
                sort($validHostelMonths);

                $unbilledValidMonths = array_diff($validHostelMonths, $billedMonthNumbers);
                $hasUnbilledMonths = !empty($unbilledValidMonths);
                
                // Cleanup: Delete unpaid invoice items that are no longer in validHostelMonths
                foreach ($hostelItems as $item) {
                    $parsed = date_parse($item->installment_name);
                    $year = $parsed['year'] ? $parsed['year'] : date('Y');
                    $ym = sprintf('%04d-%02d', $year, $parsed['month']);
                    
                    if (!in_array($ym, $validHostelMonths) && $item->paid_amount <= 0) {
                        $inv = $item->invoice;
                        $item->delete();
                        // If invoice has no more items, delete invoice
                        if ($inv && $inv->items()->count() === 0) {
                            $inv->delete();
                        } else if ($inv) {
                            // recalculate invoice totals
                            $inv->subtotal = $inv->items()->sum('amount');
                            $inv->discount_amount = $inv->items()->sum('discount_amount');
                            $inv->grand_total = max(0, $inv->subtotal - $inv->discount_amount);
                            $inv->save();
                        }
                    }
                }
            }

            $isHostelActive = $student->hostelAllocation && $student->hostelAllocation->status === 'active';

            if ($isHostelActive || $hasHostelDue || ($hostelCategory && isset($hasUnbilledMonths) && $hasUnbilledMonths)) {
                $hostelCategory = FeeCategory::updateOrCreate(
                    ['name' => 'Hostel Fee'],
                    ['type' => 'monthly', 'installments_count' => 12]
                );

                $hostelCategory->validHostelMonths = $validHostelMonths ?? [];

                $hostelFeeAmount = 0;
                if ($isHostelActive) {
                    $hostelFeeAmount = $student->hostelAllocation->bed->room->cost_per_bed ?? 0;
                } else {
                    $hostelFeeAmount = $lastHostelItemAmount; // Use historical amount if released
                }

                if ($hostelFeeAmount > 0) {
                    $dynamicStructure = new FeeStructure([
                        'class_id' => $student->class_id,
                        'fee_category_id' => $hostelCategory->id,
                        'amount' => $hostelFeeAmount * 12,
                    ]);
                    $dynamicStructure->id = 'hostel';
                    $dynamicStructure->setRelation('feeCategory', $hostelCategory);
                    $feeStructures->push($dynamicStructure);
                }
            }

            // Dynamic Transport Fee Injection
            $transportCategory = FeeCategory::where('name', 'Transport Fee')->first();
            $hasTransportDue = false;
            $lastTransportItemAmount = 0;

            if ($transportCategory) {
                $transportItems = $student->invoices->flatMap->items->where('fee_category_id', $transportCategory->id);

                $lastTransportItem = $transportItems->last();
                if ($lastTransportItem) {
                    $lastTransportItemAmount = $lastTransportItem->amount;
                }

                $totalTransportBilled = $transportItems->sum('amount');
                $totalTransportPaid = $transportItems->sum('paid_amount');
                $totalTransportDiscount = $transportItems->map(function ($i) {
                    return $i->invoice ? $i->invoice->discount_amount : 0;
                })->sum();

                $hasTransportDue = ($totalTransportBilled - $totalTransportDiscount - $totalTransportPaid) > 0;

                $allowedEnd = now()->endOfYear()->startOfMonth();
                $maxBilledTimestamp = $transportItems->max(function($item) {
                    return strtotime($item->installment_name);
                });
                if ($maxBilledTimestamp) {
                    $maxBilledDate = \Carbon\Carbon::createFromTimestamp($maxBilledTimestamp)->startOfMonth();
                    if ($maxBilledDate->year >= $allowedEnd->year && $maxBilledDate->month == 12) {
                         $allowedEnd = $maxBilledDate->copy()->addYear()->endOfYear()->startOfMonth();
                    } elseif ($maxBilledDate->year > $allowedEnd->year) {
                         $allowedEnd = $maxBilledDate->copy()->endOfYear()->startOfMonth();
                    }
                }

                $validTransportMonths = [];
                if ($student->transportAllocations->isNotEmpty()) {
                    foreach ($student->transportAllocations as $alloc) {
                        $start = $alloc->effective_from ? $alloc->effective_from->copy()->startOfMonth() : now()->startOfMonth();
                        $end = $alloc->status === 'cancelled' ? $alloc->effective_to->copy()->startOfMonth() : $allowedEnd->copy();

                        $current = $start->copy();
                        while ($current <= $end) {
                            $validTransportMonths[] = $current->format('Y-m');
                            $current->addMonth();
                        }
                    }
                } elseif (isset($hasTransportHistory) && $hasTransportHistory) { // TODO check if history exists
                    $firstItem = $transportItems->sortBy(function($item) {
                        return strtotime($item->installment_name);
                    })->first();
                    if ($firstItem) {
                        $parsed = date_parse($firstItem->installment_name);
                        $year = $parsed['year'] ? $parsed['year'] : date('Y');
                        $start = \Carbon\Carbon::createFromDate($year, $parsed['month'], 1)->startOfMonth();

                        $current = $start->copy();
                        while ($current <= $allowedEnd) {
                            $validTransportMonths[] = $current->format('Y-m');
                            $current->addMonth();
                        }
                    }
                }
                $validTransportMonths = array_unique($validTransportMonths);
                sort($validTransportMonths);

                $billedMonthNumbers = [];
                $paidMonthNumbers = [];
                foreach ($transportItems as $item) {
                    $parsed = date_parse($item->installment_name);
                    $year = $parsed['year'] ? $parsed['year'] : date('Y');
                    $ym = sprintf('%04d-%02d', $year, $parsed['month']);
                    $billedMonthNumbers[] = $ym;
                    
                    if ($item->paid_amount > 0) {
                        $paidMonthNumbers[] = $ym;
                    }
                }
                
                // Keep paid months in validTransportMonths even if allocation date changed
                $validTransportMonths = array_unique(array_merge($validTransportMonths, $paidMonthNumbers));
                sort($validTransportMonths);

                $unbilledValidMonths = array_diff($validTransportMonths, $billedMonthNumbers);
                $hasUnbilledMonths = !empty($unbilledValidMonths);
                
                // Cleanup: Delete unpaid invoice items that are no longer in validTransportMonths
                foreach ($transportItems as $item) {
                    $parsed = date_parse($item->installment_name);
                    $year = $parsed['year'] ? $parsed['year'] : date('Y');
                    $ym = sprintf('%04d-%02d', $year, $parsed['month']);
                    
                    if (!in_array($ym, $validTransportMonths) && $item->paid_amount <= 0) {
                        $inv = $item->invoice;
                        $item->delete();
                        
                        // If invoice has no more items, delete invoice
                        if ($inv && $inv->items()->count() === 0) {
                            $inv->delete();
                        } else if ($inv) {
                            // recalculate invoice totals
                            $inv->subtotal = $inv->items()->sum('amount');
                            $inv->discount_amount = $inv->items()->sum('discount_amount');
                            $inv->grand_total = max(0, $inv->subtotal - $inv->discount_amount);
                            $inv->save();
                        }
                    }
                }
            }

            $isTransportActive = $student->transportAllocation && $student->transportAllocation->status === 'active';

            if ($isTransportActive || $hasTransportDue || ($transportCategory && isset($hasUnbilledMonths) && $hasUnbilledMonths)) {
                $transportCategory = FeeCategory::updateOrCreate(
                    ['name' => 'Transport Fee'],
                    ['type' => 'monthly', 'installments_count' => 12]
                );

                $transportCategory->validTransportMonths = $validTransportMonths ?? [];

                $transportFeeAmount = 0;
                if ($isTransportActive) {
                    $transportFeeAmount = $student->transportAllocation->monthly_fee ?? 0;
                } else {
                    $transportFeeAmount = $lastTransportItemAmount; // Use historical amount if released
                }

                if ($transportFeeAmount > 0) {
                    $dynamicStructure = new FeeStructure([
                        'class_id' => $student->class_id,
                        'fee_category_id' => $transportCategory->id,
                        'amount' => $transportFeeAmount * 12,
                    ]);
                    $dynamicStructure->id = 'transport';
                    $dynamicStructure->setRelation('feeCategory', $transportCategory);
                    $feeStructures->push($dynamicStructure);
                }
            }

            // Dynamic Food Fee Injection
            if ($student->is_food_enabled) {
                $foodCategory = FeeCategory::where('name', 'Food Fee')->first();
                $hasFoodDue = false;
                $lastFoodItemAmount = 0;

                if ($foodCategory) {
                    $foodItems = $student->invoices->flatMap->items->where('fee_category_id', $foodCategory->id);

                    $lastFoodItem = $foodItems->last();
                    if ($lastFoodItem) {
                        $lastFoodItemAmount = $lastFoodItem->amount;
                    }

                    $totalFoodBilled = $foodItems->sum('amount');
                    $totalFoodPaid = $foodItems->sum('paid_amount');
                    $totalFoodDiscount = $foodItems->map(function ($i) {
                        return $i->invoice ? $i->invoice->discount_amount : 0;
                    })->sum();

                    $hasFoodDue = ($totalFoodBilled - $totalFoodDiscount - $totalFoodPaid) > 0;

                    $allowedEnd = now()->endOfYear()->startOfMonth();
                    $maxBilledTimestamp = $foodItems->max(function($item) {
                        return strtotime($item->installment_name);
                    });
                    if ($maxBilledTimestamp) {
                        $maxBilledDate = \Carbon\Carbon::createFromTimestamp($maxBilledTimestamp)->startOfMonth();
                        if ($maxBilledDate->year >= $allowedEnd->year && $maxBilledDate->month == 12) {
                             $allowedEnd = $maxBilledDate->copy()->addYear()->endOfYear()->startOfMonth();
                        } elseif ($maxBilledDate->year > $allowedEnd->year) {
                             $allowedEnd = $maxBilledDate->copy()->endOfYear()->startOfMonth();
                        }
                    }

                    $validFoodMonths = [];
                    if ($student->foodAllocation) {
                        $alloc = $student->foodAllocation;
                        $start = $alloc->start_date ? \Carbon\Carbon::parse($alloc->start_date)->startOfMonth() : now()->startOfMonth();
                        $end = $alloc->status === 'inactive' && $alloc->end_date ? \Carbon\Carbon::parse($alloc->end_date)->startOfMonth() : $allowedEnd->copy();

                        $current = $start->copy();
                        while ($current <= $end) {
                            $validFoodMonths[] = $current->format('Y-m');
                            $current->addMonth();
                        }
                    }
                    $validFoodMonths = array_unique($validFoodMonths);
                    sort($validFoodMonths);

                    $billedMonthNumbers = [];
                    $paidMonthNumbers = [];
                    foreach ($foodItems as $item) {
                        $parsed = date_parse($item->installment_name);
                        $year = $parsed['year'] ? $parsed['year'] : date('Y');
                        $ym = sprintf('%04d-%02d', $year, $parsed['month']);
                        $billedMonthNumbers[] = $ym;
                        
                        if ($item->paid_amount > 0) {
                            $paidMonthNumbers[] = $ym;
                        }
                    }
                    
                    $validFoodMonths = array_unique(array_merge($validFoodMonths, $paidMonthNumbers));
                    sort($validFoodMonths);

                    $unbilledValidFoodMonths = array_diff($validFoodMonths, $billedMonthNumbers);
                    $hasUnbilledFoodMonths = !empty($unbilledValidFoodMonths);
                    
                    // Cleanup: Delete unpaid invoice items that are no longer in validFoodMonths
                    foreach ($foodItems as $item) {
                        $parsed = date_parse($item->installment_name);
                        $year = $parsed['year'] ? $parsed['year'] : date('Y');
                        $ym = sprintf('%04d-%02d', $year, $parsed['month']);
                        
                        if (!in_array($ym, $validFoodMonths) && $item->paid_amount <= 0) {
                            $inv = $item->invoice;
                            $item->delete();
                            
                            // If invoice has no more items, delete invoice
                            if ($inv && $inv->items()->count() === 0) {
                                $inv->delete();
                            } else if ($inv) {
                                // recalculate invoice totals
                                $inv->subtotal = $inv->items()->sum('amount');
                                $inv->discount_amount = $inv->items()->sum('discount_amount');
                                $inv->grand_total = max(0, $inv->subtotal - $inv->discount_amount);
                                $inv->save();
                            }
                        }
                    }
                }

                $isFoodActive = $student->is_food_enabled && $student->foodAllocation && $student->foodAllocation->status === 'active';

                if ($isFoodActive || $hasFoodDue || ($foodCategory && isset($hasUnbilledFoodMonths) && $hasUnbilledFoodMonths)) {
                    $foodCategory = FeeCategory::firstOrCreate(
                        ['name' => 'Food Fee'],
                        ['type' => 'monthly', 'installments_count' => 12]
                    );

                    $foodCategory->validFoodMonths = $validFoodMonths ?? [];

                    $foodFeeAmount = 0;
                    if ($isFoodActive) {
                        $foodFeeAmount = $student->foodAllocation->monthly_fee ?? $student->foodAllocation->foodPlan?->monthly_fee ?? 0;
                    } else {
                        $foodFeeAmount = $lastFoodItemAmount;
                    }

                    if ($foodFeeAmount > 0) {
                        $dynamicStructure = new FeeStructure([
                            'class_id' => $student->class_id,
                            'fee_category_id' => $foodCategory->id,
                            'amount' => $foodFeeAmount * 12,
                        ]);
                        $dynamicStructure->id = 'food';
                        $dynamicStructure->setRelation('feeCategory', $foodCategory);
                        $feeStructures->push($dynamicStructure);
                    }
                }
            }

            $invoices = $student->invoices;
            $totalBilled = $feeStructures->sum('amount') - $invoices->sum('discount_amount');
            $totalPaid = $invoices->sum('paid_amount');
            $totalDue = max(0, $totalBilled - $totalPaid);

            $totalAdvance = $invoices->sum(function($inv) {
                return max(0, $inv->paid_amount - $inv->grand_total);
            });
        }

        return view('admin.fees.collection', compact('classes', 'student', 'invoices', 'totalBilled', 'totalPaid', 'totalDue', 'totalAdvance', 'feeStructures'));
    }

    public function searchStudents(Request $request)
    {
        $query = StudentProfile::with(['user', 'schoolClass']);

        if ($request->class_id) {
            $query->where('class_id', $request->class_id);
        }

        if ($request->roll_no) {
            $query->where('roll_no', $request->roll_no);
        }

        $students = $query->where('status', 'active')->get()->map(function ($s) {
            return [
                'id' => $s->id,
                'name' => $s->user->name ?? 'Unknown',
                'roll_no' => $s->roll_no,
                'class' => $s->schoolClass->name ?? '',
                'photo' => $s->photo_path,
            ];
        });

        return response()->json($students);
    }

    public function dueReport(Request $request)
    {
        $classes = SchoolClass::all();
        $dueStudents = collect();

        $selectedClassId = $request->class_id;

        if ($selectedClassId) {
            // Get total structure amount for this class (excluding Hostel Fee)
            $feeStructures = FeeStructure::where('class_id', $selectedClassId)
                ->whereHas('feeCategory', function ($q) {
                    $q->whereNotIn('name', ['Hostel Fee', 'Transport Fee', 'Food Fee']);
                })->get();
            $baseTotalBilled = $feeStructures->sum('amount');

            // Get students in this class
            $students = StudentProfile::with(['user', 'invoices', 'parent', 'hostelAllocation.bed.room', 'transportAllocation', 'foodAllocation.foodPlan'])
                ->where('class_id', $selectedClassId)
                ->where('status', 'active')
                ->get();

            foreach ($students as $student) {
                // Add student specific hostel fee if applicable
                $studentBilled = $baseTotalBilled;
                if ($student->hostelAllocation && $student->hostelAllocation->status === 'active') {
                    $hostelFeeAmount = $student->hostelAllocation->bed->room->cost_per_bed ?? 0;
                    $studentBilled += ($hostelFeeAmount * 12);
                }
                
                // Add student specific transport fee if applicable
                if ($student->transportAllocation && $student->transportAllocation->status === 'active') {
                    $transportFeeAmount = $student->transportAllocation->monthly_fee ?? 0;
                    $studentBilled += ($transportFeeAmount * 12);
                }

                // Add student specific food fee if applicable
                if ($student->is_food_enabled && $student->foodAllocation && $student->foodAllocation->status === 'active') {
                    $foodFeeAmount = $student->foodAllocation->monthly_fee ?? $student->foodAllocation->foodPlan?->monthly_fee ?? 0;
                    $studentBilled += ($foodFeeAmount * 12);
                }

                $totalDiscount = $student->invoices->sum('discount_amount');
                $totalBilled = $studentBilled - $totalDiscount;
                $totalPaid = $student->invoices->sum('paid_amount');
                $totalDue = max(0, $totalBilled - $totalPaid);

                if ($totalDue > 0) {
                    $dueStudents->push((object)[
                        'id' => $student->id,
                        'name' => $student->user->name ?? 'Unknown',
                        'roll_no' => $student->roll_no,
                        'phone' => $student->parent->phone ?? 'N/A',
                        'totalBilled' => $totalBilled,
                        'totalPaid' => $totalPaid,
                        'totalDue' => $totalDue,
                    ]);
                }
            }
        }

        return view('admin.fees.due_report', compact('classes', 'dueStudents', 'selectedClassId'));
    }

    public function storeCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:fee_categories,name',
            'type' => 'required|string',
            'installments_count' => 'required|integer|min:1',
        ]);

        $category = FeeCategory::create($request->only('name', 'type', 'installments_count'));

        if ($request->has('amount') && is_array($request->amount)) {
            foreach ($request->amount as $classId => $amount) {
                if (is_numeric($amount) && $amount >= 0) {
                    FeeStructure::create([
                        'fee_category_id' => $category->id,
                        'class_id' => $classId,
                        'amount' => $amount,
                    ]);
                }
            }
        }

        return back()->with('success', 'Fee category and amounts saved successfully!');
    }

    public function editCategory($id)
    {
        $category = FeeCategory::findOrFail($id);
        return view('admin.fees.edit_category', compact('category'));
    }

    public function updateCategory(Request $request, $id)
    {
        $category = FeeCategory::findOrFail($id);
        $request->validate([
            'name' => 'required|string|unique:fee_categories,name,' . $category->id,
            'type' => 'required|string',
        ]);

        $category->update($request->only('name', 'type', 'installments_count'));
        return back()->with('success', 'Fee category updated successfully!');
    }

    public function destroyCategory($id)
    {
        $category = FeeCategory::findOrFail($id);
        $category->delete();
        return back()->with('success', 'Fee category deleted successfully!');
    }

    public function updateAmounts(Request $request, $id)
    {
        $request->validate([
            'amounts' => 'required|array',
            'amounts.*' => 'nullable|numeric|min:0',
        ]);

        $category = FeeCategory::findOrFail($id);

        foreach ($request->amounts as $classId => $amount) {
            if (is_numeric($amount) && $amount >= 0) {
                FeeStructure::updateOrCreate(
                    ['fee_category_id' => $category->id, 'class_id' => $classId],
                    ['amount' => $amount]
                );
            } else {
                FeeStructure::where('fee_category_id', $category->id)->where('class_id', $classId)->delete();
            }
        }

        return back()->with('success', 'Class amounts updated successfully!');
    }

    public function storeStructure(Request $request)
    {
        $request->validate([
            'fee_category_id' => 'required|exists:fee_categories,id',
            'class_id' => 'required|exists:classes,id',
            'amount' => 'required|numeric|min:0',
        ]);

        FeeStructure::create($request->all());
        return back()->with('success', 'Fee structure configured successfully!');
    }

    public function generateInvoices(FeeInvoiceRequest $request)
    {
        $generated = $this->feeService->generateInvoices(
            $request->class_id,
            $request->fee_category_id,
            $request->amount,
            $request->due_date
        );

        if ($generated) {
            return back()->with('success', 'Invoices generated for all active students in the selected class!');
        }

        return back()->with('error', 'No active students found in the selected class.');
    }

    public function showInvoice($id)
    {
        $invoice = $this->feeService->getInvoiceDetails($id);
        return view('admin.fees.invoice-show', compact('invoice'));
    }

    public function collectPayment(Request $request, $id)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required|string',
            'transaction_id' => 'nullable|string',
        ]);

        $this->feeService->collectPayment(
            $id,
            $request->amount,
            $request->payment_method,
            $request->transaction_id
        );

        return back()->with('success', 'Payment recorded and posted to ledgers successfully!')
                     ->with('active_tab', 'collectTab')
                     ->with('active_structure', $request->structure_id);
    }

    public function destroyPayment($id)
    {
        $payment = \App\Models\Payment::findOrFail($id);
        $invoice = $payment->invoice;

        if (!$invoice) {
            return back()->with('error', 'Invoice not found for this payment.');
        }

        // Deduct paid amount from Invoice
        $invoice->paid_amount -= $payment->amount;

        // Reverse distribute payment from items
        $remainingAmountToDeduct = $payment->amount;
        foreach ($invoice->items()->orderBy('id', 'desc')->get() as $item) {
            if ($remainingAmountToDeduct <= 0) break;

            if ($item->paid_amount > 0) {
                $deductFromThisItem = min($item->paid_amount, $remainingAmountToDeduct);
                $item->paid_amount -= $deductFromThisItem;

                if ($item->paid_amount < $item->amount) {
                    $item->status = 'unpaid';
                }
                $item->save();
                $remainingAmountToDeduct -= $deductFromThisItem;
            }
        }

        // Update status of Invoice
        if ($invoice->paid_amount <= 0) {
            $invoice->paid_amount = 0;
            $invoice->status = 'unpaid';
        } elseif ($invoice->paid_amount >= $invoice->grand_total) {
            $invoice->status = 'paid';
        } else {
            $invoice->status = 'partially_paid';
        }
        $invoice->save();

        // Delete ledger transactions related to this payment
        \App\Models\Transaction::where('reference_no', $payment->payment_number)->delete();

        // Delete the payment record
        $payment->delete();

        return back()->with('success', 'Payment deleted successfully. Ledger and due amounts updated!');
    }

    public function directCollect(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:student_profiles,id',
            'fee_category_id' => 'required|exists:fee_categories,id',
            'amount' => 'required|numeric|min:1',
            'base_amount' => 'required|numeric|min:1',
            'payment_method' => 'required|string',
        ]);

        // Create Invoice on the fly
        $invoiceNo = 'INV-' . strtoupper(uniqid());

        // Handle Discount
        $discountAmount = 0;
        if ($request->filled('discount') && $request->discount > 0) {
            if ($request->discount_type === 'percent') {
                $discountAmount = ($request->base_amount * $request->discount) / 100;
            } else {
                $discountAmount = $request->discount;
            }
        }

        $invoice = Invoice::create([
            'student_profile_id' => $request->student_id,
            'invoice_number' => $invoiceNo,
            'issue_date' => now()->format('Y-m-d'),
            'due_date' => $request->payment_date ?? now()->format('Y-m-d'),
            'subtotal' => $request->base_amount,
            'discount_amount' => $discountAmount,
            'grand_total' => max(0, $request->base_amount - $discountAmount),
            'paid_amount' => 0.00,
            'status' => 'unpaid',
        ]);

        \App\Models\InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'fee_category_id' => $request->fee_category_id,
            'amount' => $request->base_amount,
            'installment_name' => $request->installment_name,
        ]);

        // Collect payment
        $this->feeService->collectPayment(
            $invoice->id,
            $request->amount,
            $request->payment_method,
            $request->transaction_id
        );

        return back()->with('success', 'Fee collected successfully!')
                     ->with('active_tab', 'collectTab')
                     ->with('active_structure', $request->structure_id);
    }

    public function refundAdvance($id)
    {
        $invoice = Invoice::findOrFail($id);
        $advance = $invoice->paid_amount - $invoice->grand_total;

        if ($advance <= 0) {
            return back()->with('error', 'No advance to refund for this invoice.');
        }

        // Deduct advance from invoice
        $invoice->paid_amount -= $advance;

        if ($invoice->paid_amount <= 0) {
            $invoice->paid_amount = 0;
            $invoice->status = 'unpaid';
        } elseif ($invoice->paid_amount >= $invoice->grand_total) {
            $invoice->status = 'paid';
        } else {
            $invoice->status = 'partially_paid';
        }
        $invoice->save();

        // Reverse ledger entries
        $cashLedger = \App\Models\Ledger::where('code', '1001')->first();
        $revenueLedger = \App\Models\Ledger::where('code', '4001')->first();

        if ($cashLedger && $revenueLedger) {
            \App\Models\Transaction::create([
                'ledger_id' => $cashLedger->id,
                'reference_no' => 'REF-ADV-' . $invoice->invoice_number,
                'date' => now()->format('Y-m-d'),
                'type' => 'credit',
                'amount' => $advance,
                'description' => "Refunded advance amount for invoice {$invoice->invoice_number}",
            ]);

            \App\Models\Transaction::create([
                'ledger_id' => $revenueLedger->id,
                'reference_no' => 'REF-ADV-' . $invoice->invoice_number,
                'date' => now()->format('Y-m-d'),
                'type' => 'debit',
                'amount' => $advance,
                'description' => "Refunded advance tuition fee revenue for invoice {$invoice->invoice_number}",
            ]);
        }

        return back()->with('success', 'Advance refunded successfully!');
    }

    public function generateInstallments(Request $request, $id)
    {
        $category = FeeCategory::findOrFail($id);

        if ($category->installments()->count() > 0) {
            $category->installments()->delete();
        }

        $months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

        for ($i = 1; $i <= $category->installments_count; $i++) {
            if ($category->type === 'monthly' && $category->installments_count == 12) {
                $name = $months[$i - 1];
            } elseif ($category->type === 'half-yearly' && $category->installments_count == 2) {
                $name = $i == 1 ? 'First Half' : 'Second Half';
            } elseif ($category->installments_count == 1) {
                $name = 'Full Payment';
            } else {
                $name = 'Installment ' . $i;
            }

            \App\Models\FeeInstallment::create([
                'fee_category_id' => $category->id,
                'name' => $name,
                'due_date' => null,
                'is_active' => true,
            ]);
        }

        return back()
            ->with('success', 'Installments auto-generated successfully!')
            ->with('active_accordion', 'fee-' . $category->id)
            ->with('active_tab', 'installments-' . $category->id);
    }

    public function updateInstallment(Request $request, $id)
    {
        $installment = \App\Models\FeeInstallment::findOrFail($id);

        $request->validate([
            'name' => 'required|string',
            'due_date' => 'nullable|date',
        ]);

        $installment->update([
            'name' => $request->name,
            'due_date' => $request->due_date,
        ]);

        return back()
            ->with('success', 'Installment updated successfully!')
            ->with('active_accordion', 'fee-' . $installment->fee_category_id)
            ->with('active_tab', 'installments-' . $installment->fee_category_id);
    }

    public function destroyInstallment($id)
    {
        $installment = \App\Models\FeeInstallment::findOrFail($id);
        $categoryId = $installment->fee_category_id;
        $installment->delete();

        return back()
            ->with('success', 'Installment deleted successfully!')
            ->with('active_accordion', 'fee-' . $categoryId)
            ->with('active_tab', 'installments-' . $categoryId);
    }

    public function toggleInstallment($id)
    {
        $installment = \App\Models\FeeInstallment::findOrFail($id);
        $installment->update(['is_active' => !$installment->is_active]);

        return back()
            ->with('success', 'Installment status updated successfully!')
            ->with('active_accordion', 'fee-' . $installment->fee_category_id)
            ->with('active_tab', 'installments-' . $installment->fee_category_id);
    }

    public function printReceipt($id)
    {
        $invoice = Invoice::with(['studentProfile.user', 'studentProfile.schoolClass', 'items.feeCategory', 'payments'])->findOrFail($id);
        return view('admin.fees.receipt', compact('invoice'));
    }
}
