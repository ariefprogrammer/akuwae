<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->uuid('reviewer_id');
            $table->uuid('reviewee_id');
            $table->tinyInteger('rating')->check('rating BETWEEN 1 AND 5');
            $table->text('comment')->nullable();
            $table->timestamps();
            
            $table->foreign('order_id')
                  ->references('id')
                  ->on('orders')
                  ->onDelete('cascade');
                  
            $table->foreign('reviewer_id')
                  ->references('id')
                  ->on('users');
                  
            $table->foreign('reviewee_id')
                  ->references('id')
                  ->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ratings');
    }
};