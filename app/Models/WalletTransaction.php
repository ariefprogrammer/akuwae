<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    protected $table = 'wallet_transactions';
    
    protected $fillable = [
        'wallet_id',
        'transaction_type',
        'amount',
        'reference_id',
        'description'
    ];
    
    protected $casts = [
        'amount' => 'decimal:2',
    ];
    
    public function wallet()
    {
        return $this->belongsTo(Wallet::class, 'wallet_id', 'id');
    }
}