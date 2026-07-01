<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Driver extends Model
{
    use HasUuids;
    
    protected $table = 'drivers';
    
    protected $fillable = [
        'user_id',
        'name',
        'vehicle_type',
        'vehicle_plate',
        'verification_status',
        'is_online',
        'last_activity_at',
        'current_latitude',
        'current_longitude'
    ];
    
    protected $casts = [
        'is_online' => 'boolean',
        'last_activity_at' => 'datetime',
        'current_latitude' => 'decimal:8',
        'current_longitude' => 'decimal:8',
    ];
    
    // ========== RELATIONS ==========
    
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function document()
    {
        return $this->hasOne(DriverDocument::class, 'driver_id', 'id');
    }
    
    public function orders()
    {
        return $this->hasMany(Order::class, 'driver_id', 'id');
    }
    
    // Helper methods
    public function isVerified()
    {
        return $this->verification_status === 'approved';
    }
    
    public function isPending()
    {
        return $this->verification_status === 'pending';
    }
}