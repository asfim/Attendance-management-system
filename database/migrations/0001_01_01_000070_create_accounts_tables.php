<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ledgers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // Account Code
            $table->string('name'); // e.g. Cash, Accounts Receivable, Tuition Revenue
            $table->string('type'); // asset, liability, equity, revenue, expense
            $table->timestamps();
        });

        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ledger_id')->constrained('ledgers')->onDelete('cascade');
            $table->string('reference_no')->nullable();
            $table->date('date');
            $table->string('type'); // debit, credit
            $table->decimal('amount', 10, 2);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('ledgers');
    }
};
