<?php

namespace App\Livewire\Customer;

use App\Models\Customer;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class Profile extends Component
{
    use WithFileUploads;

    public ?Customer $customer = null;

    public string $name         = '';
    public string $phone_number = '';
    public        $photo        = null;
    public string $currentPhoto = '';

    // Ganti PIN
    public string $current_pin     = '';
    public string $new_pin         = '';
    public string $new_pin_confirm = '';

    public string $successMessage = '';
    public string $pinError       = '';
    public string $pinSuccess     = '';

    public function mount()
    {
        $user = auth()->user();
        $this->customer     = $user->customer;
        $this->name         = $this->customer->name;
        $this->phone_number = $user->phone_number;
        $this->currentPhoto = $this->customer->photo ?? '';
    }

    public function save()
    {
        $this->successMessage = '';

        $this->validate([
            'name'  => 'required|string|max:100',
            'photo' => 'nullable|image|max:2048',
        ]);

        $data = ['name' => $this->name];

        if ($this->photo) {
            if ($this->currentPhoto) {
                Storage::disk('public')->delete($this->currentPhoto);
            }
            $data['photo']     = $this->photo->store('customers/photos', 'public');
            $this->currentPhoto = $data['photo'];
            $this->photo        = null;
        }

        $this->customer->update($data);
        $this->successMessage = 'Profil berhasil diperbarui.';
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
        return view('livewire.customer.profile')
            ->layout('layouts.app', ['title' => 'Profil Saya']);
    }
}