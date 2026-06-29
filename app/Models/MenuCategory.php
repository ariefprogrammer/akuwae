<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuCategory extends Model
{
    protected $table = 'menu_categories';
    
    protected $fillable = [
        'tenant_id',
        'category_name'
    ];
    
    // ========== RELATIONS ==========
    
    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id', 'id');
    }
    
    public function menus()
    {
        return $this->hasMany(Menu::class, 'menu_category_id', 'id');
    }
}