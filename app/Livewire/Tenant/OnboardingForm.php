<?php

namespace App\Livewire\Tenant;

use App\Models\Tenant;
use Livewire\Component;
use Livewire\WithFileUploads;

class OnboardingForm extends Component
{
    use WithFileUploads;

    public string $store_name    = '';
    public string $address       = '';
    public string $category      = '';
    public string $latitude      = '';
    public string $longitude     = '';
    public array  $operational_hours = [];
    public        $store_photo   = null;
    public string $error         = '';

    // Jam operasional default
    public function mount()
    {
        $days = ['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'];
        foreach ($days as $day) {
            $this->operational_hours[$day] = [
                'open'   => true,
                'start'  => '08:00',
                'end'    => '21:00',
            ];
        }

        // Kalau sudah punya tenant profile, redirect ke dashboard
        $tenant = auth()->user()->tenant;
        if ($tenant) {
            return redirect()->route('tenant.dashboard');
        }
    }

    public function save()
    {
        $this->error = '';

        $this->validate([
            'store_name'  => 'required|string|max:100',
            'address'     => 'required|string',
            'category'    => 'required|string|max:50',
            'latitude'    => 'required|numeric|between:-90,90',
            'longitude'   => 'required|numeric|between:-180,180',
            'store_photo' => 'nullable|image|max:2048',
        ], [
            'latitude.required'  => 'Pin lokasi tokomu di peta.',
            'longitude.required' => 'Pin lokasi tokomu di peta.',
        ]);

        $photoPath = null;
        if ($this->store_photo) {
            $photoPath = $this->store_photo->store('tenants/photos', 'public');
        }

        Tenant::create([
            'id'                 => \Illuminate\Support\Str::uuid(),
            'user_id'            => auth()->id(),
            'store_name'         => $this->store_name,
            'address'            => $this->address,
            'latitude'           => (float) $this->latitude,
            'longitude'          => (float) $this->longitude,
            'category'           => $this->category,
            'operational_hours'  => $this->operational_hours,
            'verification_status'=> 'pending',
        ]);

        return redirect()->route('tenant.dashboard');
    }

    public function toggleDay(string $day)
    {
        $this->operational_hours[$day]['open'] = !$this->operational_hours[$day]['open'];
    }

    public function render()
    {
        return view('livewire.tenant.onboarding-form')
            ->layout('layouts.app', ['title' => 'Setup Toko']);
    }
}