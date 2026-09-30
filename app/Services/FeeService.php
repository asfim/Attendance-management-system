<?php

namespace App\Services;

use App\Repositories\Contracts\FeeRepositoryInterface;
use App\Models\Ledger;
use App\Models\Transaction;

class FeeService
{
    protected FeeRepositoryInterface $feeRepository;

    public function __construct(FeeRepositoryInterface $feeRepository)
    {
        $this->feeRepository = $feeRepository;
    }

    public function generateInvoices(int $classId, int $feeCategoryId, float $amount, string $dueDate): bool
    {
        return $this->feeRepository->generateInvoicesForClass($classId, $feeCategoryId, $amount, $dueDate);
    }

    public function collectPayment(int $invoiceId, float $amount, string $method, ?string $transactionId)
    {
        $payment = $this->feeRepository->recordPayment($invoiceId, $amount, $method, $transactionId);

        // Find or create Ledgers for double entry accounting posting
        $cashLedger = Ledger::firstOrCreate(
            ['code' => '1001'],
            ['name' => 'Cash Account', 'type' => 'asset']
        );

        $revenueLedger = Ledger::firstOrCreate(
            ['code' => '4001'],
            ['name' => 'Tuition Fee Revenue', 'type' => 'revenue']
        );

        // Post Debit to Cash
        Transaction::create([
            'ledger_id' => $cashLedger->id,
            'reference_no' => $payment->payment_number,
            'date' => now()->format('Y-m-d'),
            'type' => 'debit',
            'amount' => $amount,
            'description' => "Collected fee payment {$payment->payment_number} via {$method}",
        ]);

        // Post Credit to Tuition Revenue
        Transaction::create([
            'ledger_id' => $revenueLedger->id,
            'reference_no' => $payment->payment_number,
            'date' => now()->format('Y-m-d'),
            'type' => 'credit',
            'amount' => $amount,
            'description' => "Tuition fee revenue from payment {$payment->payment_number}",
        ]);

        return $payment;
    }

    public function getInvoiceDetails(int $invoiceId)
    {
        return $this->feeRepository->getInvoiceDetails($invoiceId);
    }

    public function getStudentInvoices(int $studentProfileId)
    {
        return $this->feeRepository->getStudentInvoices($studentProfileId);
    }
}
