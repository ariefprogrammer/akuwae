<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkingBalanceTransaction extends Model
{
    protected $fillable = [
        'working_balance_id', 'type', 'amount', 'order_id', 'description',
    ];

    protected $casts = ['amount' => 'float'];

    public function workingBalance()
    {
        return $this->belongsTo(WorkingBalance::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}