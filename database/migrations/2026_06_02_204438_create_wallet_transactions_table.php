<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('wallet_id');
            $table->enum('transaction_type', ['topup', 'payment', 'payout', 'refund']);
            $table->decimal('amount', 12, 2);
            $table->string('reference_id', 100)->nullable();
            $table->text('description')->nullable();
            $table->timestamps(); // created_at saja (PRD minta created_at)
            
            $table->foreign('wallet_id')
                  ->references('id')
                  ->on('wallets')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};