<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_types', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g. "First Term", "Final Exam"
            $table->foreignId('session_id')->constrained('academic_sessions')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['name', 'session_id']);
        });

        Schema::create('exam_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_type_id')->constrained('exam_types')->onDelete('cascade');
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->date('exam_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->foreignId('classroom_id')->constrained('classrooms')->onDelete('cascade');
            $table->decimal('max_marks', 5, 2)->default(100.00);
            $table->decimal('pass_marks', 5, 2)->default(33.00);
            $table->timestamps();
        });

        Schema::create('marks_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_schedule_id')->constrained('exam_schedules')->onDelete('cascade');
            $table->foreignId('student_profile_id')->constrained('student_profiles')->onDelete('cascade');
            $table->decimal('marks_obtained', 5, 2)->nullable();
            $table->string('attendance_status')->default('present'); // present, absent
            $table->string('remarks')->nullable();
            $table->timestamps();
            $table->unique(['exam_schedule_id', 'student_profile_id'], 'schedule_student_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marks_entries');
        Schema::dropIfExists('exam_schedules');
        Schema::dropIfExists('exam_types');
    }
};
