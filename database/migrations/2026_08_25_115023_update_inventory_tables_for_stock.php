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
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->string('sku')->nullable()->after('name');
            $table->decimal('purchase_price', 10, 2)->default(0)->after('unit');
            $table->integer('min_stock')->default(5)->after('stock_qty');
            $table->string('location')->nullable()->after('min_stock');
        });

        Schema::table('inventory_purchases', function (Blueprint $table) {
            $table->string('payment_status')->default('unpaid')->after('status');
            $table->string('payment_method')->nullable()->after('payment_status');
            $table->string('invoice_number')->nullable()->after('payment_method');
            $table->string('attachment')->nullable()->after('invoice_number');
            $table->text('notes')->nullable()->after('attachment');
        });

        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('inventory_items')->onDelete('cascade');
            $table->enum('type', ['in', 'out']);
            $table->integer('quantity');
            $table->date('transaction_date');
            $table->string('reference')->nullable();
            $table->string('department')->nullable();
            $table->string('purpose')->nullable();
            $table->string('issued_by')->nullable();
            $table->string('received_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_transactions');

        Schema::table('inventory_purchases', function (Blueprint $table) {
            $table->dropColumn(['payment_status', 'payment_method', 'invoice_number', 'attachment', 'notes']);
        });

        Schema::table('inventory_items', function (Blueprint $table) {
            $table->dropColumn(['sku', 'purchase_price', 'min_stock', 'location']);
        });
    }
};
