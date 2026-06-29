<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TopupRequest extends Model
{
    protected $table = 'topup_requests';
    
    protected $fillable = [
        'customer_id',
        'amount',
        'status',
        'approved_by'
    ];
    
    protected $casts = [
        'amount' => 'decimal:2',
    ];
    
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }
    
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by', 'id');
    }
    
    public function isPending()
    {
        return $this->status === 'pending';
    }
    
    public function approve($adminId)
    {
        $this->status = 'approved';
        $this->approved_by = $adminId;
        $this->save();
        
        // Tambahkan saldo ke wallet customer
        $wallet = $this->customer->user->wallet;
        $wallet->addBalance($this->amount, 'Topup via admin');
    }
}