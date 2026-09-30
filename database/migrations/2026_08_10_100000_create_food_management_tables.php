<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('food_settings');
        Schema::dropIfExists('food_fee_adjustments');
        Schema::dropIfExists('food_attendances');
        Schema::dropIfExists('student_foods');
        Schema::dropIfExists('food_plans');
        Schema::dropIfExists('meals');

        // 1. Meals Table
        Schema::create('meals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained('schools')->nullOnDelete();
            $table->string('name'); // Breakfast, Lunch, Dinner, etc.
            $table->string('code')->nullable(); // BF-01, LN-01
            $table->string('time')->nullable(); // e.g. "08:00 AM"
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        // 2. Food Plans Table (Food Fee Setup)
        Schema::create('food_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained('schools')->nullOnDelete();
            $table->string('name'); // e.g. Residential Plan, Non-Residential Plan
            $table->foreignId('academic_session_id')->nullable()->constrained('academic_sessions')->nullOnDelete();
            $table->foreignId('school_class_id')->nullable()->constrained('classes')->nullOnDelete();
            $table->string('student_category')->nullable(); // Residential, Non-Residential, Special
            $table->decimal('monthly_fee', 10, 2)->default(0.00);
            $table->integer('billing_days')->default(30);
            $table->date('effective_from')->nullable();
            $table->date('effective_to')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        // 3. Student Foods Table (Links student to food plan)
        Schema::create('student_foods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_profile_id')->constrained('student_profiles')->onDelete('cascade');
            $table->foreignId('food_plan_id')->nullable()->constrained('food_plans')->nullOnDelete();
            $table->decimal('monthly_fee', 10, 2)->default(0.00);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        // 4. Food Attendances Table
        Schema::create('food_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_profile_id')->constrained('student_profiles')->onDelete('cascade');
            $table->foreignId('meal_id')->nullable()->constrained('meals')->onDelete('cascade');
            $table->date('attendance_date');
            $table->enum('status', ['taken', 'not_taken', 'leave', 'holiday', 'not_applicable'])->default('taken');
            $table->string('remarks')->nullable();
            $table->timestamps();

            $table->unique(['student_profile_id', 'meal_id', 'attendance_date'], 'unique_student_meal_date');
        });

        // 5. Food Fee Adjustments Table
        Schema::create('food_fee_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_profile_id')->constrained('student_profiles')->onDelete('cascade');
            $table->integer('month');
            $table->integer('year');
            $table->integer('non_consumption_days')->default(0);
            $table->decimal('per_day_cost', 10, 2)->default(0.00);
            $table->decimal('calculated_deduction', 10, 2)->default(0.00);
            $table->decimal('manual_adjustment', 10, 2)->default(0.00);
            $table->decimal('final_food_fee', 10, 2)->default(0.00);
            $table->foreignId('adjusted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('reason')->nullable();
            $table->timestamps();

            $table->unique(['student_profile_id', 'month', 'year'], 'unique_student_month_adjustment');
        });

        // 6. Food Settings Table
        Schema::create('food_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained('schools')->nullOnDelete();
            $table->string('key');
            $table->text('value')->nullable();
            $table->timestamps();

            $table->unique(['school_id', 'key']);
        });

        // 7. Add is_food_enabled to student_profiles if not exists
        if (!Schema::hasColumn('student_profiles', 'is_food_enabled')) {
            Schema::table('student_profiles', function (Blueprint $table) {
                $table->boolean('is_food_enabled')->default(false)->after('status');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('student_profiles', 'is_food_enabled')) {
            Schema::table('student_profiles', function (Blueprint $table) {
                $table->dropColumn('is_food_enabled');
            });
        }

        Schema::dropIfExists('food_settings');
        Schema::dropIfExists('food_fee_adjustments');
        Schema::dropIfExists('food_attendances');
        Schema::dropIfExists('student_foods');
        Schema::dropIfExists('food_plans');
        Schema::dropIfExists('meals');
    }
};
