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
        Schema::create('admission_applications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('session_id')->nullable();
            $table->unsignedBigInteger('class_id')->nullable();
            $table->unsignedBigInteger('section_id')->nullable();
            $table->string('name');
            $table->string('email');
            $table->date('admission_date');
            $table->date('dob');
            $table->string('gender');
            $table->string('blood_group')->nullable();
            $table->text('medical_info')->nullable();
            $table->string('photo_path')->nullable();
            $table->string('signature_path')->nullable();
            
            $table->string('parent_name');
            $table->string('parent_email');
            $table->string('parent_phone');
            $table->string('parent_occupation')->nullable();
            $table->text('parent_address');
            
            $table->enum('status', ['pending', 'accepted', 'rejected'])->default('pending');
            $table->timestamps();

            $table->foreign('session_id')->references('id')->on('academic_sessions')->onDelete('set null');
            $table->foreign('class_id')->references('id')->on('classes')->onDelete('set null');
            $table->foreign('section_id')->references('id')->on('sections')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admission_applications');
    }
};
