<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_custom_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->text('item_description');
            $table->decimal('estimated_weight', 5, 2);
            $table->decimal('actual_weight', 5, 2)->nullable();
            $table->decimal('base_fare_snapshot', 12, 2);
            $table->decimal('weight_fare_snapshot', 12, 2);
            $table->decimal('distance_fare_snapshot', 12, 2);
            $table->timestamps();
            
            $table->foreign('order_id')
                  ->references('id')
                  ->on('orders')
                  ->onDelete('cascade');
                  
            $table->unique('order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_custom_details');
    }
};