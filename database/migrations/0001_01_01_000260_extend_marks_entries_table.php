<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('marks_entries', function (Blueprint $table) {
            $table->decimal('written_marks', 5, 2)->nullable();
            $table->decimal('mcq_marks', 5, 2)->nullable();
            $table->decimal('practical_marks', 5, 2)->nullable();
            $table->decimal('assignment_marks', 5, 2)->nullable();
            $table->decimal('project_marks', 5, 2)->nullable();
            $table->decimal('attendance_marks', 5, 2)->nullable();
            $table->decimal('grace_marks', 5, 2)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('marks_entries', function (Blueprint $table) {
            $table->dropColumn([
                'written_marks',
                'mcq_marks',
                'practical_marks',
                'assignment_marks',
                'project_marks',
                'attendance_marks',
                'grace_marks'
            ]);
        });
    }
};
