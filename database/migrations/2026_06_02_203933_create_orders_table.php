<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 50)->unique();
            $table->uuid('customer_id');
            $table->uuid('driver_id')->nullable();
            $table->enum('service_type', ['custom', 'makan', 'antar']);
            $table->enum('status', ['finding_driver', 'processing', 'pickup', 'delivering', 'completed', 'cancelled']);
            $table->enum('payment_method', ['tunai', 'tolongpay']);
            $table->enum('payment_status', ['unpaid', 'paid', 'refunded'])->default('unpaid');
            $table->decimal('total_fare', 12, 2);
            $table->decimal('driver_earnings', 12, 2);
            $table->decimal('platform_commission', 12, 2);
            $table->timestamps();
            
            $table->foreign('customer_id')
                  ->references('id')
                  ->on('customers');
                  
            $table->foreign('driver_id')
                  ->references('id')
                  ->on('drivers');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};