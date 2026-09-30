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
        // 1. Update biometric_devices table
        Schema::table('biometric_devices', function (Blueprint $table) {
            if (!Schema::hasColumn('biometric_devices', 'serial_number')) {
                $table->string('serial_number')->nullable()->unique()->after('name');
            }
            if (!Schema::hasColumn('biometric_devices', 'comm_key')) {
                $table->string('comm_key')->default('0')->after('port');
            }
            if (!Schema::hasColumn('biometric_devices', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('location');
            }
            if (!Schema::hasColumn('biometric_devices', 'last_connected_at')) {
                $table->timestamp('last_connected_at')->nullable()->after('status');
            }
            if (!Schema::hasColumn('biometric_devices', 'last_error')) {
                $table->text('last_error')->nullable()->after('last_sync_at');
            }
        });

        // 2. Update biometric_device_logs table
        Schema::table('biometric_device_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('biometric_device_logs', 'device_id')) {
                $table->foreignId('device_id')->nullable()->constrained('biometric_devices')->nullOnDelete()->after('id');
            }
            if (!Schema::hasColumn('biometric_device_logs', 'verify_type')) {
                $table->string('verify_type')->nullable()->after('punch_state');
            }
            if (!Schema::hasColumn('biometric_device_logs', 'unique_key')) {
                $table->string('unique_key')->nullable()->unique()->after('verify_type');
            }
        });

        // 3. Update attendances table for late_minutes, early_leave_minutes, overtime_minutes
        Schema::table('attendances', function (Blueprint $table) {
            if (!Schema::hasColumn('attendances', 'late_minutes')) {
                $table->integer('late_minutes')->default(0)->after('exit_time');
            }
            if (!Schema::hasColumn('attendances', 'early_leave_minutes')) {
                $table->integer('early_leave_minutes')->default(0)->after('late_minutes');
            }
            if (!Schema::hasColumn('attendances', 'overtime_minutes')) {
                $table->integer('overtime_minutes')->default(0)->after('early_leave_minutes');
            }
        });

        // 4. Create device_sync_logs table
        if (!Schema::hasTable('device_sync_logs')) {
            Schema::create('device_sync_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('device_id')->constrained('biometric_devices')->cascadeOnDelete();
                $table->string('sync_type')->default('manual'); // manual, scheduled
                $table->timestamp('started_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->integer('total_records')->default(0);
                $table->integer('new_records')->default(0);
                $table->integer('duplicate_records')->default(0);
                $table->integer('unmapped_records')->default(0);
                $table->integer('failed_records')->default(0);
                $table->string('status')->default('running'); // running, completed, failed
                $table->text('error_message')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_sync_logs');

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn(['late_minutes', 'early_leave_minutes', 'overtime_minutes']);
        });

        Schema::table('biometric_device_logs', function (Blueprint $table) {
            $table->dropColumn(['device_id', 'verify_type', 'unique_key']);
        });

        Schema::table('biometric_devices', function (Blueprint $table) {
            $table->dropColumn(['serial_number', 'comm_key', 'is_active', 'last_connected_at', 'last_error']);
        });
    }
};
