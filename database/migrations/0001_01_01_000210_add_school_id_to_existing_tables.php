<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'users',
            'classes',
            'academic_sessions',
            'invoices',
            'books',
            'hostels',
            'transport_routes',
            'inventory_items'
        ];

        foreach ($tables as $t) {
            Schema::table($t, function (Blueprint $table) {
                $table->foreignId('school_id')->nullable()->constrained('schools')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        $tables = [
            'users',
            'classes',
            'academic_sessions',
            'invoices',
            'books',
            'hostels',
            'transport_routes',
            'inventory_items'
        ];

        foreach ($tables as $t) {
            Schema::table($t, function (Blueprint $table) {
                $table->dropForeign(['school_id']);
                $table->dropColumn('school_id');
            });
        }
    }
};
