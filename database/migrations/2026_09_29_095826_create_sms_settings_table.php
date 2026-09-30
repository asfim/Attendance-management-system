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
        Schema::create('sms_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_active')->default(false);
            $table->string('gateway_provider')->default('bulksmsbd')->comment('bulksmsbd, greenweb, bdbulksms, custom');
            $table->string('api_key')->nullable();
            $table->string('sender_id')->nullable();
            $table->text('custom_api_url')->nullable()->comment('Only if custom gateway is used');
            $table->text('entry_message_template')->nullable();
            $table->text('exit_message_template')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sms_settings');
    }
};
