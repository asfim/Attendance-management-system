<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parent_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('occupation')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->timestamps();
        });

        Schema::create('student_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->constrained('parent_profiles')->onDelete('set null');
            $table->string('roll_no')->index();
            $table->foreignId('session_id')->constrained('academic_sessions')->onDelete('cascade');
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->foreignId('section_id')->constrained('sections')->onDelete('cascade');
            $table->string('admission_no')->unique();
            $table->date('admission_date');
            $table->date('dob');
            $table->string('gender');
            $table->string('blood_group')->nullable();
            $table->text('medical_info')->nullable();
            $table->text('qr_code')->nullable();
            $table->string('status')->default('active'); // active, suspended, alumni
            $table->timestamps();
        });



        Schema::create('staff_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('department')->nullable(); // HR, Accounting, IT, Reception, Library
            $table->string('designation')->nullable();
            $table->date('joining_date');
            $table->decimal('salary', 10, 2);
            $table->string('status')->default('active'); // active, inactive
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_profiles');
        Schema::dropIfExists('student_profiles');
        Schema::dropIfExists('parent_profiles');
    }
};
