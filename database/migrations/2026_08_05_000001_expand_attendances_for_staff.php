<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->string('status')->default('present')->change(); // widen: present,absent,late,half_day,leave,holiday,work_from_home
            $table->text('late_reason')->nullable()->after('remarks');
            $table->text('leave_reason')->nullable()->after('late_reason');
            $table->string('leave_attachment')->nullable()->after('leave_reason');
            $table->string('approved_by')->nullable()->after('leave_attachment');
            $table->decimal('salary_deduction', 10, 2)->nullable()->after('approved_by');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn(['late_reason', 'leave_reason', 'leave_attachment', 'approved_by', 'salary_deduction']);
        });
    }
};
