<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visitors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained('schools')->onDelete('cascade');
            $table->string('name');
            $table->string('phone');
            $table->string('purpose');
            $table->datetime('check_in');
            $table->datetime('check_out')->nullable();
            $table->timestamps();
        });

        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained('schools')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // employee to visit
            $table->string('visitor_name');
            $table->string('purpose');
            $table->datetime('scheduled_at');
            $table->string('status')->default('pending'); // pending, approved, cancelled
            $table->timestamps();
        });

        Schema::create('gate_passes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained('schools')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // student/staff leaving
            $table->string('pass_number')->unique();
            $table->string('purpose');
            $table->datetime('issued_at');
            $table->datetime('expires_at');
            $table->timestamps();
        });

        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained('schools')->onDelete('cascade');
            $table->string('complainant_type'); // student, staff, guardian
            $table->unsignedBigInteger('complainant_id'); // ID referencing student_profiles/staff_profiles/parent_profiles
            $table->string('subject');
            $table->text('description');
            $table->text('resolution')->nullable();
            $table->string('status')->default('pending'); // pending, resolved
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('complaints');
        Schema::dropIfExists('gate_passes');
        Schema::dropIfExists('appointments');
        Schema::dropIfExists('visitors');
    }
};
