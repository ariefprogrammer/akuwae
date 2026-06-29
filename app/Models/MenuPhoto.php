<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuPhoto extends Model
{
    protected $table = 'menu_photos';
    
    protected $fillable = [
        'menu_id',
        'photo_url'
    ];
    
    // ========== RELATIONS ==========
    
    public function menu()
    {
        return $this->belongsTo(Menu::class, 'menu_id', 'id');
    }
}