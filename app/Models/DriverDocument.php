<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DriverDocument extends Model
{
    protected $table = 'driver_documents';
    
    protected $fillable = [
        'driver_id',
        'ktp_number',
        'sim_number',
        'stnk_photo',
        'selfie_ktp_photo'
    ];
    
    // ========== RELATIONS ==========
    
    public function driver()
    {
        return $this->belongsTo(Driver::class, 'driver_id', 'id');
    }
}