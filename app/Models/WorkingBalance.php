<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkingBalance extends Model
{
    protected $fillable = ['user_id', 'balance'];

    protected $casts = ['balance' => 'float'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(WorkingBalanceTransaction::class)->orderByDesc('created_at');
    }

    public static function getOrCreateFor(string $userId): self
    {
        return self::firstOrCreate(['user_id' => $userId], ['balance' => 0]);
    }

    public function deduct(float $amount, ?int $orderId = null, string $description = ''): void
    {
        $this->decrement('balance', $amount);

        WorkingBalanceTransaction::create([
            'working_balance_id' => $this->id,
            'type'                => 'commission_deduction',
            'amount'              => -$amount,
            'order_id'            => $orderId,
            'description'         => $description,
        ]);
    }

    public function topup(float $amount, string $description = ''): void
    {
        $this->increment('balance', $amount);

        WorkingBalanceTransaction::create([
            'working_balance_id' => $this->id,
            'type'                => 'topup',
            'amount'              => $amount,
            'description'         => $description,
        ]);
    }
}