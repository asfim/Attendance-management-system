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
        if (!Schema::hasColumn('student_profiles', 'biometric_id')) {
            Schema::table('student_profiles', function (Blueprint $table) {
                $table->string('biometric_id')->nullable()->index()->after('user_id');
            });
        }

        if (!Schema::hasColumn('staff_profiles', 'biometric_id')) {
            Schema::table('staff_profiles', function (Blueprint $table) {
                $table->string('biometric_id')->nullable()->index()->after('user_id');
            });
        }

        if (!Schema::hasTable('biometric_device_logs')) {
            Schema::create('biometric_device_logs', function (Blueprint $table) {
                $table->id();
                $table->string('device_sn')->nullable();
                $table->string('biometric_id')->index();
                $table->dateTime('punch_time');
                $table->string('punch_state')->default('check_in'); // check_in, check_out
                $table->string('user_type')->nullable(); // student, staff, unknown
                $table->string('user_name')->nullable();
                $table->string('status')->default('success'); // success, failed, ignored
                $table->string('message')->nullable();
                $table->text('raw_payload')->nullable();
                $table->string('ip_address')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('biometric_device_logs');

        if (Schema::hasColumn('staff_profiles', 'biometric_id')) {
            Schema::table('staff_profiles', function (Blueprint $table) {
                $table->dropColumn('biometric_id');
            });
        }

        if (Schema::hasColumn('student_profiles', 'biometric_id')) {
            Schema::table('student_profiles', function (Blueprint $table) {
                $table->dropColumn('biometric_id');
            });
        }
    }
};
