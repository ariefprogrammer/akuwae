<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SavedAddress extends Model
{
    protected $table = 'saved_addresses';
    
    protected $fillable = [
        'customer_id',
        'label',
        'address_text',
        'latitude',
        'longitude'
    ];
    
    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];
    
    // ========== RELATIONS ==========
    
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }
}