<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disputes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->uuid('reporter_id');
            $table->text('issue_description');
            $table->string('proof_photo', 255)->nullable();
            $table->enum('status', ['open', 'investigating', 'resolved'])->default('open');
            $table->enum('resolution', ['refund', 'warning', 'voucher_compensation', 'rejected'])->nullable();
            $table->uuid('resolved_by')->nullable();
            $table->timestamps();
            
            $table->foreign('order_id')
                  ->references('id')
                  ->on('orders')
                  ->onDelete('cascade');
                  
            $table->foreign('reporter_id')
                  ->references('id')
                  ->on('users');
                  
            $table->foreign('resolved_by')
                  ->references('id')
                  ->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disputes');
    }
};