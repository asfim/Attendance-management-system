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
        Schema::create('promotion_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_profile_id')->constrained('student_profiles')->cascadeOnDelete();
            
            // Old Information
            $table->foreignId('old_session_id')->constrained('academic_sessions')->cascadeOnDelete();
            $table->foreignId('old_class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('old_section_id')->constrained('sections')->cascadeOnDelete();
            $table->string('old_roll_no');
            
            // New Information
            $table->foreignId('new_session_id')->nullable()->constrained('academic_sessions')->nullOnDelete();
            $table->foreignId('new_class_id')->nullable()->constrained('classes')->nullOnDelete();
            $table->foreignId('new_section_id')->nullable()->constrained('sections')->nullOnDelete();
            $table->string('new_roll_no')->nullable();
            
            // Metadata
            $table->foreignId('promoted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status'); // e.g., 'promoted', 'graduated', 'rolled_back'
            $table->text('reason')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotion_logs');
    }
};
