<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Timetable;
use App\Models\BookIssue;
use App\Models\Notice;
use App\Services\ExamService;
use App\Services\FeeService;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class StudentController extends Controller
{
    protected ExamService $examService;
    protected FeeService $feeService;

    public function __construct(ExamService $examService, FeeService $feeService)
    {
        $this->examService = $examService;
        $this->feeService = $feeService;
    }

    public function dashboard()
    {
        $student = Auth::user()->studentProfile;

        $notices = Notice::whereIn('target_audience', ['all', 'students'])
            ->orderBy('published_at', 'desc')
            ->take(5)
            ->get();

        $booksIssued = BookIssue::where('user_id', Auth::id())
            ->where('status', 'issued')
            ->with('book')
            ->get();

        $recentInvoices = Invoice::where('student_profile_id', $student->id)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('student.dashboard', compact('student', 'notices', 'booksIssued', 'recentInvoices'));
    }

    public function routine()
    {
        $student = Auth::user()->studentProfile;

        $routine = Timetable::where('section_id', $student->section_id)
            ->where('session_id', $student->session_id)
            ->with(['subject', 'staffProfile.user', 'classroom'])
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        return view('student.routine', compact('routine'));
    }

    public function attendance()
    {
        $student = Auth::user()->studentProfile;

        if (!$student) {
            abort(404, 'Student profile not found.');
        }

        $attendances = \App\Models\Attendance::where('attendable_type', \App\Models\StudentProfile::class)
            ->where('attendable_id', $student->id)
            ->orderBy('attendance_date', 'desc')
            ->paginate(30);

        return view('student.attendance', compact('attendances'));
    }

    public function fees()
    {
        $student = Auth::user()->studentProfile;

        if (!$student) {
            return view('student.fees', [
                'student'         => null,
                'invoices'        => collect(),
                'grouped'         => collect(),
                'totalSubtotal'   => 0,
                'totalDiscount'   => 0,
                'totalNetBilled'  => 0,
                'totalPaid'       => 0,
                'totalDue'        => 0,
                'paymentHistory'  => collect(),
            ]);
        }

        // Load ALL invoices (full history, all sessions/years)
        $invoices = \App\Models\Invoice::where('student_profile_id', $student->id)
            ->with(['items.feeCategory', 'payments.invoice.items.feeCategory'])
            ->orderBy('issue_date', 'desc')
            ->get();

        // Attach discount & net calculations to each item
        $allItems = $invoices->flatMap(function ($inv) {
            $invSubtotal = max(0.01, $inv->subtotal);
            $invDiscount = $inv->discount_amount ?? 0;

            return $inv->items->map(function ($item) use ($inv, $invSubtotal, $invDiscount) {
                // Calculate item's proportional discount
                $itemDiscount = ($invDiscount > 0 && $invSubtotal > 0)
                    ? round(($item->amount / $invSubtotal) * $invDiscount, 2)
                    : 0;

                $itemNet = max(0, $item->amount - $itemDiscount);
                $itemPaid = $item->paid_amount ?? 0;
                
                // If invoice is fully paid, treat item paid as net amount
                if ($inv->status === 'paid' && $itemPaid < $itemNet) {
                    $itemPaid = $itemNet;
                }

                $itemDue = max(0, $itemNet - $itemPaid);

                // Attach dynamic calculated properties
                $item->discount_calculated = $itemDiscount;
                $item->net_amount           = $itemNet;
                $item->calculated_due       = $itemDue;
                $item->calculated_paid      = $itemPaid;

                if ($itemPaid >= $itemNet && $itemNet > 0) {
                    $item->calculated_status = 'paid';
                } elseif ($itemPaid > 0) {
                    $item->calculated_status = 'partial';
                } else {
                    $item->calculated_status = $item->status ?? 'unpaid';
                }

                return $item;
            });
        })->filter(fn($item) => $item->feeCategory);

        $grouped = $allItems->groupBy(fn($item) => $item->feeCategory->name)
            ->map(function ($items, $categoryName) {
                $totalBase     = $items->sum('amount');
                $totalDiscount = $items->sum('discount_calculated');
                $totalNet      = $items->sum('net_amount');
                $totalPaid     = $items->sum('calculated_paid');
                $totalDue      = $items->sum('calculated_due');

                return [
                    'name'          => $categoryName,
                    'type'          => $items->first()->feeCategory->type,
                    'count'         => $items->count(),
                    'totalBase'     => $totalBase,
                    'totalDiscount' => $totalDiscount,
                    'totalNet'      => $totalNet,
                    'totalPaid'     => $totalPaid,
                    'totalDue'      => $totalDue,
                    'items'         => $items->sortBy('due_date')->values(),
                ];
            })->values();

        // Payment history — all payments across all invoices
        $paymentHistory = $invoices->flatMap(fn($inv) => $inv->payments)
            ->sortByDesc('payment_date')
            ->values();

        $totalSubtotal  = $invoices->sum('subtotal');
        $totalDiscount  = $invoices->sum('discount_amount');
        $totalNetBilled = $invoices->sum('grand_total');
        $totalPaid      = $invoices->sum('paid_amount');
        $totalDue       = max(0, $totalNetBilled - $totalPaid);

        return view('student.fees', compact(
            'student', 'invoices', 'grouped',
            'totalSubtotal', 'totalDiscount', 'totalNetBilled', 'totalPaid', 'totalDue', 'paymentHistory'
        ));
    }

    public function showPaymentScreen($id)
    {
        $invoice = Invoice::findOrFail($id);
        return view('student.payment-gateway', compact('invoice'));
    }

    public function processPayment(Request $request, $id)
    {
        $request->validate([
            'payment_method' => 'required|in:SSLCommerz',
            'amount'         => 'required|numeric|min:1',
        ]);

        $invoice = Invoice::findOrFail($id);
        $student = Auth::user()->studentProfile;
        
        $txId = 'TXN-' . strtoupper(uniqid());
        
        if ($request->payment_method === 'SSLCommerz') {
            // Setup SSLCommerz Session
            $mode = Setting::get('sslcommerz_mode', 'sandbox');
            $storeId = Setting::get('sslcommerz_store_id');
            $storePassword = Setting::get('sslcommerz_store_password');
            $currency = Setting::get('sslcommerz_currency', 'BDT');

            $url = $mode === 'live' 
                ? 'https://securepay.sslcommerz.com/gwprocess/v4/api.php'
                : 'https://sandbox.sslcommerz.com/gwprocess/v4/api.php';

            $post_data = [];
            $post_data['store_id'] = $storeId;
            $post_data['store_passwd'] = $storePassword;
            $post_data['total_amount'] = $request->amount;
            $post_data['currency'] = $currency;
            $post_data['tran_id'] = $txId;
            $post_data['success_url'] = route('student.fees.pay.success', $id);
            $post_data['fail_url'] = route('student.fees.pay.fail', $id);
            $post_data['cancel_url'] = route('student.fees.pay.cancel', $id);
            
            // Customer Info
            $post_data['cus_name'] = $student->user->name;
            $post_data['cus_email'] = $student->user->email ?? 'student@example.com';
            $post_data['cus_add1'] = 'Dhaka';
            $post_data['cus_add2'] = 'Dhaka';
            $post_data['cus_city'] = 'Dhaka';
            $post_data['cus_state'] = 'Dhaka';
            $post_data['cus_postcode'] = '1000';
            $post_data['cus_country'] = 'Bangladesh';
            $post_data['cus_phone'] = '01700000000';
            $post_data['cus_fax'] = '01700000000';

            // Shipment
            $post_data['shipping_method'] = "NO";
            $post_data['num_of_item'] = 1;
            $post_data['product_name'] = "School Fee";
            $post_data['product_category'] = "Fee";
            $post_data['product_profile'] = "general";
            
            // Store the original request details in session for processing later
            session([$txId => [
                'invoice_id' => $id,
                'amount' => $request->amount,
                'payment_method' => 'SSLCommerz',
            ]]);

            $response = Http::asForm()->post($url, $post_data);
            $result = $response->json();
            
            if (isset($result['status']) && $result['status'] == 'SUCCESS') {
                return redirect($result['GatewayPageURL']);
            }
            
            return redirect()->back()->with('error', 'SSLCommerz Error: ' . ($result['failedreason'] ?? 'Could not initialize payment gateway.'));
        }

    }

    public function sslSuccess(Request $request, $id)
    {
        $txId = $request->input('tran_id');
        $sessionData = session($txId);
        
        if (!$sessionData || $sessionData['invoice_id'] != $id) {
            return redirect()->route('student.fees')->with('error', 'Invalid transaction.');
        }

        // Add payment to DB
        $this->feeService->collectPayment(
            $sessionData['invoice_id'],
            $sessionData['amount'],
            $sessionData['payment_method'],
            $txId
        );

        // Clear session
        session()->forget($txId);

        return redirect()->route('student.fees')->with('success', 'Fee payment processed and ledger entries updated successfully!');
    }

    public function sslFail(Request $request, $id)
    {
        return redirect()->route('student.fees')->with('error', 'Payment failed. Please try again.');
    }

    public function sslCancel(Request $request, $id)
    {
        return redirect()->route('student.fees')->with('error', 'Payment was cancelled.');
    }

    public function results(Request $request)
    {
        $student = Auth::user()->studentProfile;
        $examTypes = \App\Models\ExamType::where('session_id', $student->session_id)->get();
        $reportCard = [];

        if ($request->filled('exam_type_id')) {
            $reportCard = $this->examService->getStudentReportCard($student->id, $request->exam_type_id);
        }

        return view('student.results', compact('examTypes', 'reportCard'));
    }

    public function transport()
    {
        $student = Auth::user()->studentProfile->load([
            'transportAllocation.route',
            'transportAllocation.stop',
        ]);

        $allocation = $student->transportAllocation;
        $histories  = $student->transportHistories()->with(['route', 'stop'])->take(10)->get();

        return view('student.transport', compact('student', 'allocation', 'histories'));
    }

    public function hostel()
    {
        $student = Auth::user()->studentProfile->load([
            'hostelAllocation.bed.room.hostel',
        ]);

        $allocation     = ($student->hostelAllocation && $student->hostelAllocation->status === 'active')
            ? $student->hostelAllocation
            : null;
        $allAllocations = $student->hostelAllocations()->with('bed.room.hostel')->latest()->get();

        return view('student.hostel', compact('student', 'allocation', 'allAllocations'));
    }

    public function food()
    {
        $student = Auth::user()->studentProfile->load([
            'foodAllocation.foodPlan',
        ]);

        $activeFoodPlan = ($student->foodAllocation && $student->foodAllocation->status === 'active')
            ? $student->foodAllocation
            : null;
        $allFoodPlans = $student->foodAllocations()->with('foodPlan')->latest()->get();
        
        $foodAttendances = $student->foodAttendances()
            ->with('meal')
            ->orderBy('attendance_date', 'desc')
            ->take(30)
            ->get();

        return view('student.food', compact('student', 'activeFoodPlan', 'allFoodPlans', 'foodAttendances'));
    }

    public function books()
    {
        $userId = Auth::id();

        $bookIssues = \App\Models\BookIssue::where('user_id', $userId)
            ->with('book')
            ->orderBy('issue_date', 'desc')
            ->get();

        $activeIssued = $bookIssues->filter(fn($i) => in_array(strtolower($i->status), ['issued', 'overdue', 'pending']));
        $returned     = $bookIssues->filter(fn($i) => strtolower($i->status) === 'returned');
        $totalFine    = $bookIssues->sum('fine_amount');

        return view('student.books', compact('bookIssues', 'activeIssued', 'returned', 'totalFine'));
    }
}
