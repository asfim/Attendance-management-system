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
        // 1. Expense Categories
        Schema::create('expense_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 2. Expenses
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('expense_categories')->onDelete('cascade');
            $table->date('expense_date');
            $table->string('purpose');
            $table->text('description')->nullable();
            $table->decimal('amount', 12, 2);
            $table->string('payment_method')->nullable();
            $table->string('paid_by')->nullable();
            $table->string('reference_no')->nullable();
            $table->string('attachment')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 3. Donation Campaigns
        Schema::create('donation_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('goal_amount', 12, 2)->default(0);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->text('description')->nullable();
            $table->string('status')->default('active'); // active, completed, cancelled
            $table->timestamps();
        });

        // 4. Donations
        Schema::create('donations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->nullable()->constrained('donation_campaigns')->nullOnDelete();
            $table->string('donor_name');
            $table->string('donor_phone')->nullable();
            $table->date('donation_date');
            $table->decimal('amount', 12, 2);
            $table->string('type')->default('General'); // Zakat, Sadaqah, etc.
            $table->string('payment_method')->nullable();
            $table->string('reference_no')->nullable();
            $table->string('purpose')->nullable();
            $table->text('notes')->nullable();
            $table->string('receipt_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('donations');
        Schema::dropIfExists('donation_campaigns');
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('expense_categories');
    }
};
