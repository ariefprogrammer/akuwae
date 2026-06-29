<?php

namespace App\Livewire\Driver;

use App\Models\Driver;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class Profile extends Component
{
    public ?Driver $driver = null;

    // Ganti PIN
    public string $current_pin     = '';
    public string $new_pin         = '';
    public string $new_pin_confirm = '';

    public string $pinError   = '';
    public string $pinSuccess = '';

    public function mount()
    {
        $this->driver = auth()->user()->driver;
    }

    public function changePin()
    {
        $this->pinError   = '';
        $this->pinSuccess = '';

        $this->validate([
            'current_pin'     => 'required|digits:6',
            'new_pin'         => 'required|digits:6|different:current_pin',
            'new_pin_confirm' => 'required|same:new_pin',
        ], [
            'new_pin.different'    => 'PIN baru harus berbeda dari PIN lama.',
            'new_pin_confirm.same' => 'Konfirmasi PIN tidak cocok.',
        ]);

        $user = auth()->user();

        if (!Hash::check($this->current_pin, $user->pin)) {
            $this->pinError = 'PIN saat ini salah.';
            return;
        }

        $user->update(['pin' => Hash::make($this->new_pin)]);

        $this->current_pin     = '';
        $this->new_pin         = '';
        $this->new_pin_confirm = '';
        $this->pinSuccess      = 'PIN berhasil diubah.';
    }

    public function logout()
    {
        auth()->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function render()
    {
        return view('livewire.driver.profile')
            ->layout('layouts.app', ['title' => 'Profil Saya']);
    }
}