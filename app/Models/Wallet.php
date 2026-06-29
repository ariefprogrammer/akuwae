<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    protected $table = 'wallets';
    
    protected $fillable = [
        'user_id',
        'balance'
    ];
    
    protected $casts = [
        'balance' => 'decimal:2',
    ];
    
    // ========== RELATIONS ==========
    
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    
    public function transactions()
    {
        return $this->hasMany(WalletTransaction::class, 'wallet_id', 'id');
    }
    
    // Helper methods
    public function hasSufficientBalance($amount)
    {
        return $this->balance >= $amount;
    }
    
    public function addBalance($amount, $description = null)
    {
        $this->balance += $amount;
        $this->save();
        
        return $this->transactions()->create([
            'transaction_type' => 'topup',
            'amount' => $amount,
            'description' => $description
        ]);
    }
    
    public function deductBalance($amount, $description = null)
    {
        if (!$this->hasSufficientBalance($amount)) {
            throw new \Exception('Insufficient balance');
        }
        
        $this->balance -= $amount;
        $this->save();
        
        return $this->transactions()->create([
            'transaction_type' => 'payment',
            'amount' => -$amount,
            'description' => $description
        ]);
    }
}