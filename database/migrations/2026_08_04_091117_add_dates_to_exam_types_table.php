<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_types', function (Blueprint $table) {
            $table->date('start_date')->nullable()->after('session_id');
            $table->date('end_date')->nullable()->after('start_date');
            $table->string('status')->default('upcoming')->after('end_date'); // upcoming, ongoing, completed
        });

        // Pivot table for exam_type <-> classes
        Schema::create('exam_type_class', function (Blueprint $table) {
            $table->foreignId('exam_type_id')->constrained('exam_types')->onDelete('cascade');
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->primary(['exam_type_id', 'class_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_type_class');
        Schema::table('exam_types', function (Blueprint $table) {
            $table->dropColumn(['start_date', 'end_date', 'status']);
        });
    }
};
