<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('pin', 255)->nullable()->change();
            $table->enum('role', ['customer', 'driver', 'tenant', 'admin'])->default('customer')->change();
            $table->enum('status', ['active', 'suspended'])->default('active')->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('pin', 255)->nullable(false)->change();
            $table->enum('role', ['customer', 'driver', 'tenant', 'admin'])->default(null)->change();
            $table->enum('status', ['active', 'suspended'])->default('active')->change();
        });
    }
};