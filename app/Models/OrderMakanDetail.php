<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderMakanDetail extends Model
{
    protected $table = 'order_makan_details';
    
    protected $fillable = [
        'order_id',
        'tenant_id',
        'estimated_preparation_time'
    ];
    
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }
    
    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id', 'id');
    }
    
    public function items()
    {
        return $this->hasMany(OrderMakanItem::class, 'order_makan_detail_id', 'id');
    }
}