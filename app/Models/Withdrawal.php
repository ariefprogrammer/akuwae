<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Withdrawal extends Model
{
    protected $table = 'withdrawals';
    
    protected $fillable = [
        'user_id',
        'amount',
        'bank_name',
        'bank_account_number',
        'status',
        'processed_at'
    ];
    
    protected $casts = [
        'amount' => 'decimal:2',
        'processed_at' => 'datetime',
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    
    public function isPending()
    {
        return $this->status === 'pending';
    }
    
    public function approve()
    {
        $this->status = 'approved';
        $this->processed_at = now();
        $this->save();
    }
}