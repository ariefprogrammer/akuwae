<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM(
            'waiting_tenant',
            'preparing',
            'finding_driver',
            'processing',
            'ready',
            'pickup',
            'item_mismatch',
            'arrived',
            'delivering',
            'completed',
            'cancelled'
        ) NOT NULL");

        Schema::table('order_custom_details', function (Blueprint $table) {
            $table->text('mismatch_reason')->nullable()->after('item_description');
        });
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM(
            'waiting_tenant',
            'preparing',
            'finding_driver',
            'processing',
            'ready',
            'pickup',
            'delivering',
            'completed',
            'cancelled'
        ) NOT NULL");

        Schema::table('order_custom_details', function (Blueprint $table) {
            $table->dropColumn('mismatch_reason');
        });
    }
};