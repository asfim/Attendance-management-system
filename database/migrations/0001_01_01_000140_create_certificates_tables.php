<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->string('template_name')->unique(); // e.g. Character Certificate, Leaving Certificate, Bonafide
            $table->text('content'); // HTML content with placeholders like {name}, {roll}, {class}
            $table->timestamps();
        });

        Schema::create('issued_certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('certificate_id')->constrained('certificates')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('issue_date');
            $table->string('certificate_no')->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('issued_certificates');
        Schema::dropIfExists('certificates');
    }
};
