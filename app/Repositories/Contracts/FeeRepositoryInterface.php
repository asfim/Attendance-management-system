<?php

namespace App\Repositories\Contracts;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Collection;

interface FeeRepositoryInterface extends BaseRepositoryInterface
{
    public function generateInvoicesForClass(int $classId, int $feeCategoryId, float $amount, string $dueDate): bool;

    public function recordPayment(int $invoiceId, float $amount, string $method, ?string $transactionId): Payment;

    public function getInvoiceDetails(int $invoiceId): ?Invoice;

    public function getStudentInvoices(int $studentProfileId): Collection;
}
