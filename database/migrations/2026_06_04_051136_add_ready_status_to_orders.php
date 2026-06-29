<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

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
            'delivering',
            'completed',
            'cancelled'
        ) NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM(
            'waiting_tenant',
            'preparing',
            'finding_driver',
            'processing',
            'pickup',
            'delivering',
            'completed',
            'cancelled'
        ) NOT NULL");
    }
};