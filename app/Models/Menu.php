<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $table = 'menus';
    
    protected $fillable = [
        'menu_category_id',
        'item_name',
        'description',
        'price',
        'is_available'
    ];
    
    protected $casts = [
        'price' => 'decimal:2',
        'is_available' => 'boolean',
    ];
    
    // ========== RELATIONS ==========
    
    public function menuCategory()
    {
        return $this->belongsTo(MenuCategory::class, 'menu_category_id', 'id');
    }
    
    public function photos()
    {
        return $this->hasMany(MenuPhoto::class, 'menu_id', 'id');
    }
    
    public function tenant()
    {
        return $this->category->tenant();
    }
}