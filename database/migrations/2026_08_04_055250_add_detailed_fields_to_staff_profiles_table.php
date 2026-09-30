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
        Schema::table('staff_profiles', function (Blueprint $table) {
            $table->string('photo')->nullable();
            $table->string('bangla_name')->nullable();
            $table->string('gender')->nullable();
            $table->date('dob')->nullable();
            $table->string('religion')->nullable();
            $table->string('marital_status')->nullable();
            $table->string('national_id')->nullable();
            $table->text('permanent_address')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->string('employment_type')->nullable(); // Full-time, Part-time, Contract
            $table->string('experience')->nullable();
            $table->text('qualifications')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('staff_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'photo', 'bangla_name', 'gender', 'dob', 
                'religion', 'marital_status', 'national_id', 'permanent_address', 
                'emergency_contact_name', 'emergency_contact_phone', 'employment_type', 
                'experience', 'qualifications'
            ]);
        });
    }
};
