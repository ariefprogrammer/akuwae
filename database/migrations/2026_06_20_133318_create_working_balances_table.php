<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('working_balances', function (Blueprint $table) {
            $table->id();
            $table->char('user_id', 36);
            $table->decimal('balance', 12, 2)->default(0);
            $table->timestamps();

            $table->unique('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('working_balance_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('working_balance_id');
            $table->enum('type', ['topup', 'commission_deduction', 'adjustment']);
            $table->decimal('amount', 12, 2);
            $table->unsignedBigInteger('order_id')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->foreign('working_balance_id')->references('id')->on('working_balances')->onDelete('cascade');
            $table->foreign('order_id')->references('id')->on('orders')->nullOnDelete();
        });

        Schema::create('working_balance_topup_requests', function (Blueprint $table) {
            $table->id();
            $table->char('user_id', 36);
            $table->decimal('amount', 12, 2);
            $table->string('proof_photo')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->char('approved_by', 36)->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('working_balance_topup_requests');
        Schema::dropIfExists('working_balance_transactions');
        Schema::dropIfExists('working_balances');
    }
};