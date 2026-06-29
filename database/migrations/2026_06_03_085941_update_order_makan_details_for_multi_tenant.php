<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_makan_details', function (Blueprint $table) {
            // Hapus foreign key dulu
            $table->dropForeign('order_makan_details_order_id_foreign');
            // Hapus unique index
            $table->dropUnique('order_makan_details_order_id_unique');
            // Buat ulang foreign key tanpa unique
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('order_makan_details', function (Blueprint $table) {
            $table->dropForeign('order_makan_details_order_id_foreign');
            $table->unique('order_id');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
        });
    }
};