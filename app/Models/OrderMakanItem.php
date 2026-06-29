<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderMakanItem extends Model
{
    protected $table = 'order_makan_items';
    
    protected $fillable = [
        'order_makan_detail_id',
        'menu_id',
        'quantity',
        'notes',
        'price_snapshot'
    ];
    
    protected $casts = [
        'price_snapshot' => 'decimal:2',
    ];
    
    public function orderDetail()
    {
        return $this->belongsTo(OrderMakanDetail::class, 'order_makan_detail_id', 'id');
    }
    
    public function menu()
    {
        return $this->belongsTo(Menu::class, 'menu_id', 'id');
    }
    
    // Hitung subtotal item
    public function getSubtotalAttribute()
    {
        return $this->quantity * $this->price_snapshot;
    }
}