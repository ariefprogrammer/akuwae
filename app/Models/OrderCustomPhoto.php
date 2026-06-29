<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderCustomPhoto extends Model
{
    protected $table = 'order_custom_photos';
    
    protected $fillable = [
        'order_id',
        'photo_type',
        'photo_url'
    ];
    
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }
}