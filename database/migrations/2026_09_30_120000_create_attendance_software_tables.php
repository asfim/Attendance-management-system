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
        // 1. Branches Table
        if (!Schema::hasTable('branches')) {
            Schema::create('branches', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('code')->unique()->nullable();
                $table->string('address')->nullable();
                $table->string('phone')->nullable();
                $table->string('email')->nullable();
                $table->enum('status', ['active', 'inactive'])->default('active');
                $table->timestamps();
            });
        }

        // 2. Departments Table
        if (!Schema::hasTable('departments')) {
            Schema::create('departments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('cascade');
                $table->string('name');
                $table->string('code')->nullable();
                $table->text('description')->nullable();
                $table->enum('status', ['active', 'inactive'])->default('active');
                $table->timestamps();
            });
        }

        // 3. Designations Table
        if (!Schema::hasTable('designations')) {
            Schema::create('designations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('department_id')->nullable()->constrained('departments')->onDelete('cascade');
                $table->string('title');
                $table->text('description')->nullable();
                $table->enum('status', ['active', 'inactive'])->default('active');
                $table->timestamps();
            });
        }

        // 4. Update Staff Profiles Table
        Schema::table('staff_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('staff_profiles', 'branch_id')) {
                $table->foreignId('branch_id')->nullable()->after('user_id')->constrained('branches')->nullOnDelete();
            }
            if (!Schema::hasColumn('staff_profiles', 'department_id')) {
                $table->foreignId('department_id')->nullable()->after('branch_id')->constrained('departments')->nullOnDelete();
            }
            if (!Schema::hasColumn('staff_profiles', 'designation_id')) {
                $table->foreignId('designation_id')->nullable()->after('department_id')->constrained('designations')->nullOnDelete();
            }
            if (!Schema::hasColumn('staff_profiles', 'fingerprint_id')) {
                $table->string('fingerprint_id')->nullable()->after('biometric_id');
            }
            if (!Schema::hasColumn('staff_profiles', 'face_id')) {
                $table->string('face_id')->nullable()->after('fingerprint_id');
            }
            if (!Schema::hasColumn('staff_profiles', 'overtime_rate')) {
                $table->decimal('overtime_rate', 8, 2)->default(0)->after('salary');
            }
        });

        // 5. Update Shifts Table
        Schema::table('shifts', function (Blueprint $table) {
            if (!Schema::hasColumn('shifts', 'shift_type')) {
                $table->string('shift_type')->default('general')->after('code'); // general, morning, evening, night, flexible
            }
            if (!Schema::hasColumn('shifts', 'grace_time_minutes')) {
                $table->integer('grace_time_minutes')->default(15)->after('end_time');
            }
            if (!Schema::hasColumn('shifts', 'late_mark_after_minutes')) {
                $table->integer('late_mark_after_minutes')->default(30)->after('grace_time_minutes');
            }
            if (!Schema::hasColumn('shifts', 'early_leave_before_minutes')) {
                $table->integer('early_leave_before_minutes')->default(15)->after('late_mark_after_minutes');
            }
            if (!Schema::hasColumn('shifts', 'overtime_start_after_minutes')) {
                $table->integer('overtime_start_after_minutes')->default(30)->after('early_leave_before_minutes');
            }
            if (!Schema::hasColumn('shifts', 'half_day_hours')) {
                $table->decimal('half_day_hours', 4, 2)->default(4.00)->after('overtime_start_after_minutes');
            }
        });

        // 6. Update Attendances Table
        Schema::table('attendances', function (Blueprint $table) {
            if (!Schema::hasColumn('attendances', 'branch_id')) {
                $table->foreignId('branch_id')->nullable()->after('id')->constrained('branches')->nullOnDelete();
            }
            if (!Schema::hasColumn('attendances', 'department_id')) {
                $table->foreignId('department_id')->nullable()->after('branch_id')->constrained('departments')->nullOnDelete();
            }
            if (!Schema::hasColumn('attendances', 'shift_id')) {
                $table->foreignId('shift_id')->nullable()->after('department_id')->constrained('shifts')->nullOnDelete();
            }
            if (!Schema::hasColumn('attendances', 'is_missing_punch')) {
                $table->boolean('is_missing_punch')->default(false)->after('status');
            }
            if (!Schema::hasColumn('attendances', 'late_minutes')) {
                $table->integer('late_minutes')->default(0)->after('is_missing_punch');
            }
            if (!Schema::hasColumn('attendances', 'early_leave_minutes')) {
                $table->integer('early_leave_minutes')->default(0)->after('late_minutes');
            }
            if (!Schema::hasColumn('attendances', 'overtime_minutes')) {
                $table->integer('overtime_minutes')->default(0)->after('early_leave_minutes');
            }
            if (!Schema::hasColumn('attendances', 'working_hours')) {
                $table->decimal('working_hours', 5, 2)->default(0)->after('overtime_minutes');
            }
            if (!Schema::hasColumn('attendances', 'is_corrected')) {
                $table->boolean('is_corrected')->default(false)->after('working_hours');
            }
            if (!Schema::hasColumn('attendances', 'correction_reason')) {
                $table->text('correction_reason')->nullable()->after('is_corrected');
            }
            if (!Schema::hasColumn('attendances', 'corrected_by')) {
                $table->foreignId('corrected_by')->nullable()->after('correction_reason')->constrained('users')->nullOnDelete();
            }
        });

        // 7. Leave Balances Table
        if (!Schema::hasTable('leave_balances')) {
            Schema::create('leave_balances', function (Blueprint $table) {
                $table->id();
                $table->foreignId('staff_profile_id')->constrained('staff_profiles')->onDelete('cascade');
                $table->integer('year');
                $table->integer('casual_leave_quota')->default(10);
                $table->integer('casual_leave_used')->default(0);
                $table->integer('sick_leave_quota')->default(14);
                $table->integer('sick_leave_used')->default(0);
                $table->integer('annual_leave_quota')->default(15);
                $table->integer('annual_leave_used')->default(0);
                $table->integer('emergency_leave_quota')->default(5);
                $table->integer('emergency_leave_used')->default(0);
                $table->timestamps();
            });
        }

        // 8. Update Holidays Table
        Schema::table('holidays', function (Blueprint $table) {
            if (!Schema::hasColumn('holidays', 'type')) {
                $table->string('type')->default('company')->after('name'); // government, company, weekly, festival, custom
            }
            if (!Schema::hasColumn('holidays', 'branch_id')) {
                $table->foreignId('branch_id')->nullable()->after('type')->constrained('branches')->nullOnDelete();
            }
        });

        // 9. Employee Transfers Table
        if (!Schema::hasTable('employee_transfers')) {
            Schema::create('employee_transfers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('staff_profile_id')->constrained('staff_profiles')->onDelete('cascade');
                $table->foreignId('from_branch_id')->nullable()->constrained('branches')->nullOnDelete();
                $table->foreignId('to_branch_id')->nullable()->constrained('branches')->nullOnDelete();
                $table->foreignId('from_department_id')->nullable()->constrained('departments')->nullOnDelete();
                $table->foreignId('to_department_id')->nullable()->constrained('departments')->nullOnDelete();
                $table->date('transfer_date');
                $table->text('reason')->nullable();
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        // 10. Attendance Notifications Table
        if (!Schema::hasTable('attendance_notifications')) {
            Schema::create('attendance_notifications', function (Blueprint $table) {
                $table->id();
                $table->foreignId('staff_profile_id')->constrained('staff_profiles')->onDelete('cascade');
                $table->enum('type', ['late', 'absent', 'leave_approval', 'attendance_correction']);
                $table->enum('channel', ['sms', 'email', 'whatsapp'])->default('sms');
                $table->string('recipient')->nullable();
                $table->text('message');
                $table->enum('status', ['sent', 'failed', 'queued'])->default('sent');
                $table->timestamp('sent_at')->useCurrent();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_notifications');
        Schema::dropIfExists('employee_transfers');
        Schema::dropIfExists('leave_balances');
        Schema::dropIfExists('designations');
        Schema::dropIfExists('departments');
        Schema::dropIfExists('branches');
    }
};
