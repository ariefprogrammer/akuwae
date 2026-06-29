<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderCustomDetail extends Model
{
    protected $table = 'order_custom_details';
    
    protected $fillable = [
        'order_id',
        'vehicle_type',
        'item_description',
        'mismatch_reason',
        'estimated_weight',
        'actual_weight',
        'base_fare_snapshot',
        'weight_fare_snapshot',
        'distance_fare_snapshot',
    ];
    
    protected $casts = [
        'estimated_weight' => 'decimal:2',
        'actual_weight' => 'decimal:2',
        'base_fare_snapshot' => 'decimal:2',
        'weight_fare_snapshot' => 'decimal:2',
        'distance_fare_snapshot' => 'decimal:2',
    ];
    
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }
    
    public function photos()
    {
        return $this->hasMany(OrderCustomPhoto::class, 'order_id', 'order_id');
    }
    
    public function getTotalFareAttribute()
    {
        return $this->base_fare_snapshot + $this->weight_fare_snapshot + $this->distance_fare_snapshot;
    }
}