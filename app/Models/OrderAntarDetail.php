<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderAntarDetail extends Model
{
    protected $table = 'order_antar_details';
    
    protected $fillable = [
        'order_id',
        'requested_vehicle_type'
    ];
    
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }
}