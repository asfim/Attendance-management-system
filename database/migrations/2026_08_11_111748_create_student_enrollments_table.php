<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('student_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_profile_id')->constrained('student_profiles')->cascadeOnDelete();
            $table->foreignId('session_id')->constrained('academic_sessions')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('section_id')->constrained('sections')->cascadeOnDelete();
            $table->string('roll_no');
            $table->date('admission_date')->nullable();
            
            // Enrollment status (active, completed, graduated, transferred)
            $table->string('status')->default('active');
            
            // Pass/Fail status for promotion references
            $table->string('promotion_status')->nullable();
            
            // Self-referencing link to the previous year's enrollment
            $table->unsignedBigInteger('previous_enrollment_id')->nullable();
            $table->foreign('previous_enrollment_id')->references('id')->on('student_enrollments')->nullOnDelete();
            
            $table->timestamps();

            // Unique constraint to prevent duplicate roll numbers in the same class/section/session
            $table->unique(['session_id', 'class_id', 'section_id', 'roll_no'], 'enrollment_unique_roll');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_enrollments');
    }
};
