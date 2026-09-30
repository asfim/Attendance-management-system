<?php

namespace App\Repositories\Eloquent;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\StudentProfile;
use App\Repositories\Contracts\FeeRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class FeeRepository extends BaseRepository implements FeeRepositoryInterface
{
    public function __construct(Invoice $invoice)
    {
        parent::__construct($invoice);
    }

    public function generateInvoicesForClass(int $classId, int $feeCategoryId, float $amount, string $dueDate): bool
    {
        $students = StudentProfile::where('class_id', $classId)->where('status', 'active')->get();

        if ($students->isEmpty()) {
            return false;
        }

        DB::transaction(function () use ($students, $feeCategoryId, $amount, $dueDate) {
            foreach ($students as $student) {
                $invoiceNo = 'INV-' . strtoupper(uniqid());

                $invoice = Invoice::create([
                    'student_profile_id' => $student->id,
                    'invoice_number' => $invoiceNo,
                    'issue_date' => now()->format('Y-m-d'),
                    'due_date' => $dueDate,
                    'subtotal' => $amount,
                    'discount_amount' => 0.00,
                    'grand_total' => $amount,
                    'paid_amount' => 0.00,
                    'status' => 'unpaid',
                ]);

                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'fee_category_id' => $feeCategoryId,
                    'amount' => $amount,
                ]);
            }
        });

        return true;
    }

    public function recordPayment(int $invoiceId, float $amount, string $method, ?string $transactionId): Payment
    {
        return DB::transaction(function () use ($invoiceId, $amount, $method, $transactionId) {
            $invoice = Invoice::with('items')->findOrFail($invoiceId);
            $payNo = 'PAY-' . strtoupper(uniqid());

            $payment = Payment::create([
                'invoice_id' => $invoice->id,
                'payment_number' => $payNo,
                'payment_date' => now()->format('Y-m-d'),
                'amount' => $amount,
                'payment_method' => $method,
                'transaction_id' => $transactionId,
                'status' => 'completed',
            ]);

            $newPaidAmount = $invoice->paid_amount + $amount;
            $invoice->paid_amount = $newPaidAmount;

            // Distribute payment to items
            $remainingAmount = $amount;
            foreach ($invoice->items as $item) {
                if ($remainingAmount <= 0) break;
                
                $itemDue = $item->amount - ($item->paid_amount ?? 0);
                if ($itemDue > 0) {
                    $payForThisItem = min($itemDue, $remainingAmount);
                    $item->paid_amount = ($item->paid_amount ?? 0) + $payForThisItem;
                    if ($item->paid_amount >= $item->amount) {
                        $item->status = 'paid';
                    }
                    $item->save();
                    $remainingAmount -= $payForThisItem;
                }
            }

            if ($newPaidAmount >= $invoice->grand_total) {
                $invoice->status = 'paid';
            } elseif ($newPaidAmount > 0) {
                $invoice->status = 'partially_paid';
            } else {
                $invoice->status = 'unpaid';
            }

            $invoice->save();

            return $payment;
        });
    }

    public function getInvoiceDetails(int $invoiceId): ?Invoice
    {
        return Invoice::with(['studentProfile.user', 'studentProfile.schoolClass', 'studentProfile.section', 'items.feeCategory', 'payments'])->find($invoiceId);
    }

    public function getStudentInvoices(int $studentProfileId): Collection
    {
        return Invoice::where('student_profile_id', $studentProfileId)->with('items.feeCategory')->get();
    }
}
