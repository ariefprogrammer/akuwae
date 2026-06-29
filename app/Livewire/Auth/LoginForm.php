<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class LoginForm extends Component
{
    public string $phone_number = '';
    public string $pin = '';
    public string $error = '';

    public function login()
    {
        $this->validate([
            'phone_number' => 'required|string',
            'pin'          => 'required|digits:6',
        ]);

        $user = User::where('phone_number', $this->phone_number)
                    ->where('status', 'active')
                    ->first();

        if (!$user || !Hash::check($this->pin, $user->pin)) {
            $this->error = 'Nomor HP atau PIN salah.';
            return;
        }

        if ($user->role === 'admin') {
            $this->error = 'Akun admin tidak dapat login di sini.';
            return;
        }

        auth()->login($user);

        return redirect()->to(match($user->role) {
            'customer' => route('customer.dashboard'),
            'driver'   => route('driver.dashboard'),
            'tenant'   => route('tenant.dashboard'),
            default    => '/',
        });
    }

    public function render()
    {
        return view('livewire.auth.login-form')
            ->layout('layouts.auth', ['title' => 'Login']);
    }
}