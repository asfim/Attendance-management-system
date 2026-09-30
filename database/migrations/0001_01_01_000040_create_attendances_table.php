<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->date('attendance_date');
            $table->string('attendable_type');
            $table->unsignedBigInteger('attendable_id');
            $table->string('status'); // present, absent, late, half_day
            $table->string('remarks')->nullable();
            $table->timestamps();

            $table->index(['attendable_type', 'attendable_id']);
            $table->unique(['attendance_date', 'attendable_type', 'attendable_id'], 'date_attendable_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
