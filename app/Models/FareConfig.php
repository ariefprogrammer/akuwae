<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FareConfig extends Model
{
    protected $fillable = [
        'service_type',
        'vehicle_type',
        'base_fare',
        'per_km_fare',
        'per_kg_fare',
        'platform_commission_pct',
    ];

    protected $casts = [
        'base_fare'               => 'float',
        'per_km_fare'             => 'float',
        'per_kg_fare'             => 'float',
        'platform_commission_pct' => 'float',
    ];

    public static function getFor(string $service, string $vehicle): ?self
    {
        return self::where('service_type', $service)
                   ->where('vehicle_type', $vehicle)
                   ->first();
    }
}