<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkingBalanceTopupRequest extends Model
{
    protected $fillable = [
        'user_id', 'amount', 'proof_photo', 'status', 'approved_by',
    ];

    protected $casts = ['amount' => 'float'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}