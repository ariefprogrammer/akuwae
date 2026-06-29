<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fare_configs', function (Blueprint $table) {
            $table->decimal('per_kg_fare', 12, 2)->default(0)->after('per_km_fare');
        });

        Schema::table('order_custom_details', function (Blueprint $table) {
            $table->enum('vehicle_type', ['motor', 'mobil'])->default('motor')->after('order_id');
        });

        // Update tarif per kg untuk layanan custom
        DB::table('fare_configs')
            ->where('service_type', 'custom')
            ->where('vehicle_type', 'motor')
            ->update(['per_kg_fare' => 1000]);

        DB::table('fare_configs')
            ->where('service_type', 'custom')
            ->where('vehicle_type', 'mobil')
            ->update(['per_kg_fare' => 1500]);
    }

    public function down(): void
    {
        Schema::table('fare_configs', function (Blueprint $table) {
            $table->dropColumn('per_kg_fare');
        });

        Schema::table('order_custom_details', function (Blueprint $table) {
            $table->dropColumn('vehicle_type');
        });
    }
};