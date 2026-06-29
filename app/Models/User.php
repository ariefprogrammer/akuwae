<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use NotificationChannels\WebPush\HasPushSubscriptions;

class User extends Authenticatable implements FilamentUser, HasName
{
    use HasUuids, Notifiable, HasPushSubscriptions;

    protected $table = 'users';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'phone_number',
        'email',
        'pin',
        'password',
        'role',
        'status',
    ];

    protected $hidden = [
        'pin',
        'password',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Wajib untuk Filament: hanya role 'admin' yang boleh masuk panel
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->role === 'admin' && $this->status === 'active';
    }

    // ========== RELATIONS ==========

    public function customer()
    {
        return $this->hasOne(Customer::class, 'user_id', 'id');
    }

    public function driver()
    {
        return $this->hasOne(Driver::class, 'user_id', 'id');
    }

    public function tenant()
    {
        return $this->hasOne(Tenant::class, 'user_id', 'id');
    }

    public function wallet()
    {
        return $this->hasOne(Wallet::class, 'user_id', 'id');
    }

    public function ratingsGiven()
    {
        return $this->hasMany(Rating::class, 'reviewer_id', 'id');
    }

    public function ratingsReceived()
    {
        return $this->hasMany(Rating::class, 'reviewee_id', 'id');
    }

    public function workingBalance()
    {
        return $this->hasOne(WorkingBalance::class);
    }

    // ========== HELPERS ==========

    public function isCustomer(): bool { return $this->role === 'customer'; }
    public function isDriver(): bool   { return $this->role === 'driver'; }
    public function isTenant(): bool   { return $this->role === 'tenant'; }
    public function isAdmin(): bool    { return $this->role === 'admin'; }

    public function getFilamentName(): string
    {
        // Ambil nama dari relasi customer jika ada, fallback ke email atau phone
        return $this->customer?->name 
            ?? $this->email 
            ?? $this->phone_number 
            ?? 'Admin';
    }
}