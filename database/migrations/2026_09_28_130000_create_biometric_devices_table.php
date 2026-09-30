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
        Schema::create('biometric_devices', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g. "Main Gate K40", "Branch 1 Machine"
            $table->string('ip_address'); // e.g. "192.168.1.201"
            $table->integer('port')->default(4370);
            $table->string('device_sn')->nullable();
            $table->string('location')->nullable(); // e.g. "Branch 1 - Gate A"
            $table->string('status')->default('offline'); // online, offline, disabled
            $table->timestamp('last_sync_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('biometric_devices');
    }
};
