<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Advance Salary Records
        Schema::create('advance_salaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->text('reason')->nullable();
            $table->date('date');
            $table->string('recovery_method')->default('next_month'); // next_month, multiple_months, installments
            $table->integer('installments')->default(1);
            $table->decimal('monthly_deduction', 10, 2)->default(0);
            $table->decimal('recovered_amount', 10, 2)->default(0);
            $table->string('approved_by')->nullable();
            $table->text('remarks')->nullable();
            $table->string('status')->default('active'); // active, fully_recovered
            $table->timestamps();
        });

        // Payroll Payment History (supports partial payments)
        Schema::create('payroll_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salary_id')->constrained('salaries')->onDelete('cascade');
            $table->date('payment_date');
            $table->decimal('amount', 10, 2);
            $table->string('payment_method')->default('cash'); // cash, bank_transfer, cheque, mobile_banking
            $table->string('reference_number')->nullable();
            $table->string('paid_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Payroll Settings (office rules)
        Schema::create('payroll_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained()->onDelete('cascade');
            $table->integer('working_days')->default(26);
            $table->time('office_start_time')->default('09:00:00');
            $table->time('office_end_time')->default('17:00:00');
            $table->integer('grace_time_minutes')->default(15); // minutes after start before marked late
            $table->integer('late_deduction_minutes')->default(30); // minutes late before salary deduction
            $table->decimal('late_deduction_per_day', 10, 2)->default(0); // fixed per late day, 0 = per day salary
            $table->boolean('absent_deduction_enabled')->default(true);
            $table->boolean('half_day_deduction_enabled')->default(true);
            $table->decimal('half_day_deduction_rate', 5, 2)->default(50); // percentage of daily salary
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_settings');
        Schema::dropIfExists('payroll_payments');
        Schema::dropIfExists('advance_salaries');
    }
};
