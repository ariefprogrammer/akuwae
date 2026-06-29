<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();  
            $table->string('phone_number', 20)->unique();
            $table->string('pin', 255);     
            $table->enum('role', ['customer', 'driver', 'tenant', 'admin']);
            $table->enum('status', ['active', 'suspended'])->default('active');
            $table->timestamps();            
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};