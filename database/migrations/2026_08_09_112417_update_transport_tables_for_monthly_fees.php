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
        // Rename first if it exists
        if (Schema::hasColumn('transport_routes', 'fare')) {
            Schema::table('transport_routes', function (Blueprint $table) {
                $table->renameColumn('fare', 'default_monthly_fee');
            });
        }

        // Update existing transport_routes table
        Schema::table('transport_routes', function (Blueprint $table) {
            $table->string('route_code')->nullable()->after('route_name');
            $table->text('description')->nullable()->after('end_point');
            $table->string('status')->default('active')->after('default_monthly_fee');
            $table->text('remarks')->nullable()->after('status');
        });

        // Create new transport_route_stops table
        Schema::create('transport_route_stops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('route_id')->constrained('transport_routes')->onDelete('cascade');
            $table->string('stop_name');
            $table->integer('stop_order')->default(0);
            $table->time('pickup_time')->nullable();
            $table->time('drop_time')->nullable();
            $table->decimal('additional_fee', 10, 2)->default(0);
            $table->string('status')->default('active');
            $table->timestamps();
        });

        // Rename allocation_date first if it exists
        if (Schema::hasColumn('transport_allocations', 'allocation_date')) {
            Schema::table('transport_allocations', function (Blueprint $table) {
                $table->renameColumn('allocation_date', 'effective_from');
            });
        }

        // Update existing transport_allocations table
        Schema::table('transport_allocations', function (Blueprint $table) {
            $table->foreignId('stop_id')->nullable()->constrained('transport_route_stops')->onDelete('set null')->after('route_id');
            $table->decimal('monthly_fee', 10, 2)->default(0)->after('stop_id');
            $table->date('effective_to')->nullable()->after('effective_from');
            $table->text('remarks')->nullable()->after('status');

            // make vehicle_id and driver_id nullable since they might not be set immediately
            $table->unsignedBigInteger('vehicle_id')->nullable()->change();
            $table->unsignedBigInteger('driver_id')->nullable()->change();
        });

        // Create new transport_histories table
        Schema::create('transport_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_profile_id')->constrained('student_profiles')->onDelete('cascade');
            $table->foreignId('route_id')->nullable()->constrained('transport_routes')->onDelete('set null');
            $table->foreignId('stop_id')->nullable()->constrained('transport_route_stops')->onDelete('set null');
            $table->string('action'); // Allocated, Changed, Suspended, Reactivated, Released
            $table->string('old_value')->nullable();
            $table->string('new_value')->nullable();
            $table->date('action_date');
            $table->text('reason')->nullable();
            $table->foreignId('performed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transport_histories');
        Schema::dropIfExists('transport_route_stops');

        Schema::table('transport_allocations', function (Blueprint $table) {
            $table->dropForeign(['stop_id']);
            $table->dropColumn('stop_id');
            $table->dropColumn('monthly_fee');
            $table->renameColumn('effective_from', 'allocation_date');
            $table->dropColumn('effective_to');
            $table->dropColumn('remarks');
        });

        Schema::table('transport_routes', function (Blueprint $table) {
            $table->dropColumn('route_code');
            $table->dropColumn('description');
            $table->dropColumn('status');
            $table->dropColumn('remarks');
        });

        Schema::table('transport_routes', function (Blueprint $table) {
            $table->renameColumn('default_monthly_fee', 'fare');
        });
    }
};
