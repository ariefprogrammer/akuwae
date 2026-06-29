<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Customer extends Model
{
    use HasUuids;
    
    protected $table = 'customers';
    
    protected $fillable = [
        'user_id',
        'name',
        'photo'
    ];
    
    // ========== RELATIONS ==========
    
    // Balik ke User (One-to-One inverse)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    
    // Customer punya banyak alamat tersimpan
    public function savedAddresses()
    {
        return $this->hasMany(SavedAddress::class, 'customer_id', 'id');
    }
    
    // Customer punya banyak order
    public function orders()
    {
        return $this->hasMany(Order::class, 'customer_id', 'id');
    }
    
    // Customer punya banyak request topup
    public function topupRequests()
    {
        return $this->hasMany(TopupRequest::class, 'customer_id', 'id');
    }
}