<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\InvoiceItem;
use App\Models\FeeCategory;

class FeeReportController extends Controller
{
    public function hostelFeeReport(Request $request)
    {
        return $this->generateReport($request, 'Hostel Fee', 'Hostel Fee Report');
    }

    public function foodFeeReport(Request $request)
    {
        return $this->generateReport($request, 'Food Fee', 'Food Fee Report');
    }

    public function transportFeeReport(Request $request)
    {
        return $this->generateReport($request, 'Transport Fee', 'Transport Fee Report');
    }

    public function academicFeeReport(Request $request)
    {
        // Fetch academic fee categories (excluding Hostel, Food, Transport)
        $categories = FeeCategory::whereNotIn('name', ['Hostel Fee', 'Food Fee', 'Transport Fee'])->get();
        
        // Determine active tab
        $activeCategoryId = $request->get('category_id');
        if (!$activeCategoryId && $categories->isNotEmpty()) {
            $activeCategoryId = $categories->first()->id;
        }

        $activeCategory = $categories->firstWhere('id', $activeCategoryId);
        $categoryName = $activeCategory ? $activeCategory->name : 'Academic Fees';
        
        $query = InvoiceItem::with(['invoice.studentProfile.user', 'invoice.studentProfile.schoolClass', 'invoice.studentProfile.section'])
            ->where('fee_category_id', $activeCategoryId);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('invoice.studentProfile', function ($q) use ($search) {
                $q->where('roll_no', 'like', "%{$search}%")
                  ->orWhere('admission_no', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('class_id')) {
            $query->whereHas('invoice.studentProfile', function ($q) use ($request) {
                $q->where('class_id', $request->class_id);
            });
        }

        if ($request->filled('section_id')) {
            $query->whereHas('invoice.studentProfile', function ($q) use ($request) {
                $q->where('section_id', $request->section_id);
            });
        }

        if ($request->filled('date')) {
            $query->whereDate('due_date', $request->date);
        }

        // Calculate Totals Before Pagination
        $totalAmount = (clone $query)->sum('amount');
        $totalPaid = (clone $query)->sum('paid_amount');
        $totalDue = $totalAmount - $totalPaid;

        $items = $query->latest()->paginate(20)->withQueryString();
        
        $classes = \App\Models\SchoolClass::with('sections')->get();
        $reportTitle = 'Academic Fees Report';

        return view('admin.reports.fees.academic', compact(
            'items', 'reportTitle', 'totalAmount', 'totalPaid', 'totalDue', 
            'categoryName', 'classes', 'categories', 'activeCategoryId'
        ));
    }

    private function generateReport(Request $request, $categoryName, $reportTitle)
    {
        $feeCategoryIds = FeeCategory::where('name', 'like', '%' . $categoryName . '%')->pluck('id');

        $query = InvoiceItem::with(['invoice.studentProfile.user', 'invoice.studentProfile.schoolClass', 'invoice.studentProfile.section'])
            ->whereIn('fee_category_id', $feeCategoryIds);

        return $this->processReportQuery($request, $query, $categoryName, $reportTitle);
    }

    private function processReportQuery(Request $request, $query, $categoryName, $reportTitle)
    {

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('invoice.studentProfile', function ($q) use ($search) {
                $q->where('roll_no', 'like', "%{$search}%")
                  ->orWhere('admission_no', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('class_id')) {
            $query->whereHas('invoice.studentProfile', function ($q) use ($request) {
                $q->where('class_id', $request->class_id);
            });
        }

        if ($request->filled('section_id')) {
            $query->whereHas('invoice.studentProfile', function ($q) use ($request) {
                $q->where('section_id', $request->section_id);
            });
        }

        if ($request->filled('date')) {
            $query->whereDate('due_date', $request->date);
        }

        // Calculate Totals Before Pagination
        $totalAmount = (clone $query)->sum('amount');
        $totalPaid = (clone $query)->sum('paid_amount');
        $totalDue = $totalAmount - $totalPaid;

        $items = $query->latest()->paginate(20);
        $classes = \App\Models\SchoolClass::with('sections')->get();

        return view('admin.reports.fees.generic_fee_report', compact('items', 'reportTitle', 'totalAmount', 'totalPaid', 'totalDue', 'categoryName', 'classes'));
    }
}
