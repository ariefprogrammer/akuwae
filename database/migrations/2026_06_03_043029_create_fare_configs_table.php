<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fare_configs', function (Blueprint $table) {
            $table->id();
            $table->string('service_type'); // antar, makan, custom
            $table->string('vehicle_type'); // motor, mobil
            $table->decimal('base_fare', 12, 2)->default(0);
            $table->decimal('per_km_fare', 12, 2)->default(0);
            $table->decimal('platform_commission_pct', 5, 2)->default(20); // % komisi platform
            $table->timestamps();

            $table->unique(['service_type', 'vehicle_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fare_configs');
    }
};