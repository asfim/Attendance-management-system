<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fee_categories', function (Blueprint $table) {
            $table->string('type')->default('one-time')->after('name'); // monthly, one-time
            $table->integer('installments_count')->default(1)->after('type');
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->string('installment_name')->nullable()->after('amount'); // e.g., "January", "February", or "Full Payment"
            $table->date('due_date')->nullable()->after('installment_name');
            $table->string('status')->default('pending')->after('due_date'); // pending, paid, overdue
            $table->decimal('paid_amount', 10, 2)->default(0.00)->after('amount');
        });
    }

    public function down(): void
    {
        Schema::table('fee_categories', function (Blueprint $table) {
            $table->dropColumn(['type', 'installments_count']);
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropColumn(['installment_name', 'due_date', 'status', 'paid_amount']);
        });
    }
};
