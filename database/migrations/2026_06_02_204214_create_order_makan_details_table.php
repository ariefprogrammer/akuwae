<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_makan_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->uuid('tenant_id');
            $table->integer('estimated_preparation_time')->default(0);
            $table->timestamps();
            
            $table->foreign('order_id')
                  ->references('id')
                  ->on('orders')
                  ->onDelete('cascade');
                  
            $table->foreign('tenant_id')
                  ->references('id')
                  ->on('tenants');
                  
            $table->unique('order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_makan_details');
    }
};