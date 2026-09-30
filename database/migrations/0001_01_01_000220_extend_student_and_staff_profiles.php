<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_profiles', function (Blueprint $table) {
            $table->string('previous_school')->nullable();
            $table->string('nationality')->default('Bangladeshi');
            $table->string('religion')->nullable();
            $table->string('birth_certificate')->nullable();
            $table->string('nid')->nullable();
            $table->string('passport')->nullable();
            $table->string('house')->nullable(); // School house color/group
            $table->string('batch')->nullable();
        });


        Schema::table('staff_profiles', function (Blueprint $table) {
            $table->string('blood_group')->nullable();
            $table->string('emergency_contact')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('student_profiles', function (Blueprint $table) {
            $table->dropColumn(['previous_school', 'nationality', 'religion', 'birth_certificate', 'nid', 'passport', 'house', 'batch']);
        });


        Schema::table('staff_profiles', function (Blueprint $table) {
            $table->dropColumn(['blood_group', 'emergency_contact']);
        });
    }
};
