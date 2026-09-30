<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recruitments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained('schools')->onDelete('cascade');
            $table->string('job_title');
            $table->string('department');
            $table->integer('no_of_vacancies')->default(1);
            $table->string('status')->default('open'); // open, closed
            $table->timestamps();
        });

        Schema::create('interviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recruitment_id')->constrained('recruitments')->onDelete('cascade');
            $table->string('candidate_name');
            $table->string('candidate_email');
            $table->datetime('scheduled_at');
            $table->decimal('score', 4, 2)->nullable();
            $table->string('status')->default('pending'); // pending, passed, failed
            $table->timestamps();
        });

        Schema::create('terminations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained('schools')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('notice_date');
            $table->date('termination_date');
            $table->text('reason');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('terminations');
        Schema::dropIfExists('interviews');
        Schema::dropIfExists('recruitments');
    }
};
