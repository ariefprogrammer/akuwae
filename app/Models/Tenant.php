<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Tenant extends Model
{
    use HasUuids;
    
    protected $table = 'tenants';
    
    protected $fillable = [
        'user_id',
        'store_name',
        'address',
        'latitude',
        'longitude',
        'category',
        'operational_hours',
        'verification_status',
        'photo',
        'is_open',
    ];

    protected $casts = [
        'operational_hours' => 'array',
        'is_open'           => 'boolean',
        'latitude'          => 'float',
        'longitude'         => 'float',
    ];
    
    // ========== RELATIONS ==========
    
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    
    public function menuCategories()
    {
        return $this->hasMany(MenuCategory::class, 'tenant_id', 'id');
    }
    
    public function menus()
    {
        return $this->hasManyThrough(Menu::class, MenuCategory::class, 'tenant_id', 'menu_category_id');
    }
    
    public function orders()
    {
        return $this->hasMany(OrderMakanDetail::class, 'tenant_id', 'id');
    }
}