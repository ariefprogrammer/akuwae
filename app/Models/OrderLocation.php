<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderLocation extends Model
{
    protected $table = 'order_locations';
    
    protected $fillable = [
        'order_id',
        'origin_address',
        'origin_latitude',
        'origin_longitude',
        'destination_address',
        'destination_latitude',
        'destination_longitude',
        'distance_km',
        'notes_for_driver'
    ];
    
    protected $casts = [
        'origin_latitude' => 'decimal:8',
        'origin_longitude' => 'decimal:8',
        'destination_latitude' => 'decimal:8',
        'destination_longitude' => 'decimal:8',
        'distance_km' => 'decimal:2',
    ];
    
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }
}