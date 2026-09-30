<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salaries', function (Blueprint $table) {
            // Salary structure (allowances breakdown)
            $table->decimal('house_allowance', 10, 2)->default(0)->after('basic_salary');
            $table->decimal('medical_allowance', 10, 2)->default(0)->after('house_allowance');
            $table->decimal('transport_allowance', 10, 2)->default(0)->after('medical_allowance');
            $table->decimal('food_allowance', 10, 2)->default(0)->after('transport_allowance');
            $table->decimal('mobile_allowance', 10, 2)->default(0)->after('food_allowance');
            $table->decimal('internet_allowance', 10, 2)->default(0)->after('mobile_allowance');
            $table->decimal('special_allowance', 10, 2)->default(0)->after('internet_allowance');
            $table->decimal('festival_allowance', 10, 2)->default(0)->after('special_allowance');
            $table->decimal('other_allowances', 10, 2)->default(0)->after('festival_allowance');

            // Attendance-based deductions
            $table->decimal('absent_deduction', 10, 2)->default(0)->after('other_allowances');
            $table->decimal('late_deduction', 10, 2)->default(0)->after('absent_deduction');

            // Other deductions
            $table->decimal('loan_deduction', 10, 2)->default(0)->after('late_deduction');
            $table->decimal('advance_deduction', 10, 2)->default(0)->after('loan_deduction');
            $table->decimal('other_deduction', 10, 2)->default(0)->after('advance_deduction');
            $table->decimal('tax', 10, 2)->default(0)->after('other_deduction');
            $table->decimal('provident_fund', 10, 2)->default(0)->after('tax');

            // Additions
            $table->decimal('overtime', 10, 2)->default(0)->after('provident_fund');

            // Attendance counters
            $table->integer('working_days')->default(26)->after('overtime');
            $table->integer('present_days')->default(0)->after('working_days');
            $table->integer('absent_days')->default(0)->after('present_days');
            $table->integer('late_days')->default(0)->after('absent_days');
            $table->integer('leave_days')->default(0)->after('late_days');
            $table->integer('half_days')->default(0)->after('leave_days');

            // Payment meta
            $table->string('payment_method')->nullable()->after('payment_date');
            $table->string('payment_reference')->nullable()->after('payment_method');
            $table->decimal('paid_amount', 10, 2)->default(0)->after('payment_reference');
            $table->boolean('is_locked')->default(false)->after('paid_amount');
            $table->text('notes')->nullable()->after('is_locked');
        });
    }

    public function down(): void
    {
        Schema::table('salaries', function (Blueprint $table) {
            $table->dropColumn([
                'house_allowance', 'medical_allowance', 'transport_allowance', 'food_allowance',
                'mobile_allowance', 'internet_allowance', 'special_allowance', 'festival_allowance',
                'other_allowances', 'absent_deduction', 'late_deduction', 'loan_deduction',
                'advance_deduction', 'other_deduction', 'tax', 'provident_fund', 'overtime',
                'working_days', 'present_days', 'absent_days', 'late_days', 'leave_days', 'half_days',
                'payment_method', 'payment_reference', 'paid_amount', 'is_locked', 'notes',
            ]);
        });
    }
};
