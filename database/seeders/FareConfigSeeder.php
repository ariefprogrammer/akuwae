<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FareConfigSeeder extends Seeder
{
    public function run(): void
    {
        $configs = [
            ['service_type' => 'antar', 'vehicle_type' => 'motor', 'base_fare' => 5000,  'per_km_fare' => 3000, 'platform_commission_pct' => 20],
            ['service_type' => 'antar', 'vehicle_type' => 'mobil', 'base_fare' => 10000, 'per_km_fare' => 5000, 'platform_commission_pct' => 20],
            ['service_type' => 'makan', 'vehicle_type' => 'motor', 'base_fare' => 5000,  'per_km_fare' => 3000, 'platform_commission_pct' => 20],
            ['service_type' => 'custom','vehicle_type' => 'motor', 'base_fare' => 7000,  'per_km_fare' => 3500, 'platform_commission_pct' => 20],
            ['service_type' => 'custom','vehicle_type' => 'mobil', 'base_fare' => 12000, 'per_km_fare' => 5500, 'platform_commission_pct' => 20],
        ];

        foreach ($configs as $config) {
            DB::table('fare_configs')->insertOrIgnore($config);
        }
    }
}