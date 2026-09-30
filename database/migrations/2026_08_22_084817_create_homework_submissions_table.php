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
        Schema::create('homework_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('homework_id')->constrained('homework')->onDelete('cascade');
            $table->foreignId('student_profile_id')->constrained('student_profiles')->onDelete('cascade');
            $table->string('file_path')->nullable();
            $table->text('student_remarks')->nullable();
            $table->text('teacher_remarks')->nullable();
            $table->decimal('marks', 5, 2)->nullable();
            $table->enum('status', ['pending', 'submitted', 'evaluated', 'late'])->default('pending');
            $table->timestamps();
            
            $table->unique(['homework_id', 'student_profile_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('homework_submissions');
    }
};
