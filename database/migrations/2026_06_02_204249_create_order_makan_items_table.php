<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_makan_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_makan_detail_id');
            $table->unsignedBigInteger('menu_id');
            $table->integer('quantity');
            $table->string('notes', 255)->nullable();
            $table->decimal('price_snapshot', 12, 2);
            $table->timestamps();
            
            $table->foreign('order_makan_detail_id')
                  ->references('id')
                  ->on('order_makan_details')
                  ->onDelete('cascade');
                  
            $table->foreign('menu_id')
                  ->references('id')
                  ->on('menus');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_makan_items');
    }
};