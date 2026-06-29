<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'orders';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            $order->public_id = $order->public_id ?? (string) \Illuminate\Support\Str::uuid();
        });
    }

    // Wajib untuk route model binding pakai public_id, bukan id
    public function getRouteKeyName(): string
    {
        return 'public_id';
    }
    
    protected $fillable = [
        'order_number',
        'customer_id',
        'driver_id',
        'service_type',
        'status',
        'payment_method',
        'payment_status',
        'total_fare',
        'driver_earnings',
        'platform_commission'
    ];
    
    protected $casts = [
        'total_fare' => 'decimal:2',
        'driver_earnings' => 'decimal:2',
        'platform_commission' => 'decimal:2',
    ];
    
    // ========== RELATIONS ==========
    
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }
    
    public function driver()
    {
        return $this->belongsTo(Driver::class, 'driver_id', 'id');
    }
    
    public function location()
    {
        return $this->hasOne(OrderLocation::class, 'order_id', 'id');
    }
    
    // Polymorphic-like relations berdasarkan service_type
    public function customDetail()
    {
        return $this->hasOne(OrderCustomDetail::class, 'order_id', 'id');
    }
    
    public function makanDetail()
    {
        return $this->hasOne(OrderMakanDetail::class, 'order_id', 'id');
    }
    
    public function antarDetail()
    {
        return $this->hasOne(OrderAntarDetail::class, 'order_id', 'id');
    }
    
    public function rating()
    {
        return $this->hasOne(Rating::class, 'order_id', 'id');
    }
    
    public function dispute()
    {
        return $this->hasOne(Dispute::class, 'order_id', 'id');
    }
    
    // Helper methods
    public function isCustom()
    {
        return $this->service_type === 'custom';
    }
    
    public function isMakan()
    {
        return $this->service_type === 'makan';
    }
    
    public function isAntar()
    {
        return $this->service_type === 'antar';
    }
    
    public function isCompleted()
    {
        return $this->status === 'completed';
    }
    
    public function isPaid()
    {
        return $this->payment_status === 'paid';
    }

    public function makanDetails()
    {
        return $this->hasMany(OrderMakanDetail::class);
    }

    public function messages()
    {
        return $this->hasMany(OrderMessage::class)->orderBy('created_at');
    }
}