<?php

namespace App\Livewire\Tenant;

use App\Models\Tenant;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class StoreSettings extends Component
{
    use WithFileUploads;

    public ?Tenant $tenant = null;

    public string $store_name  = '';
    public string $address     = '';
    public string $category    = '';
    public string $latitude    = '';
    public string $longitude   = '';
    public array  $operational_hours = [];
    public        $store_photo = null;
    public string $currentPhoto = '';
    public bool   $is_open     = true;

    public string $successMessage = '';
    public string $errorMessage   = '';

    public function mount()
    {
        $tenant = auth()->user()->tenant;

        if (!$tenant || $tenant->verification_status !== 'approved') {
            return redirect()->route('tenant.dashboard');
        }

        $this->tenant       = $tenant;
        $this->store_name   = $tenant->store_name;
        $this->address      = $tenant->address;
        $this->category     = $tenant->category;
        $this->latitude     = (string) $tenant->latitude;
        $this->longitude    = (string) $tenant->longitude;
        $this->currentPhoto = $tenant->photo ?? '';
        $this->is_open      = $tenant->is_open ?? true;

        // Load jam operasional
        $days    = ['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'];
        $saved   = is_array($tenant->operational_hours)
                    ? $tenant->operational_hours
                    : json_decode($tenant->operational_hours, true);

        foreach ($days as $day) {
            $this->operational_hours[$day] = $saved[$day] ?? [
                'open'  => true,
                'start' => '08:00',
                'end'   => '21:00',
            ];
        }
    }

    public function toggleDay(string $day)
    {
        $this->operational_hours[$day]['open'] = !$this->operational_hours[$day]['open'];
    }

    public function save()
    {
        $this->successMessage = '';
        $this->errorMessage   = '';

        $this->validate([
            'store_name'  => 'required|string|max:100',
            'address'     => 'required|string',
            'category'    => 'required|string|max:50',
            'latitude'    => 'required|numeric|between:-90,90',
            'longitude'   => 'required|numeric|between:-180,180',
            'store_photo' => 'nullable|image|max:2048',
        ], [
            'latitude.required'  => 'Pin lokasi toko di peta.',
            'longitude.required' => 'Pin lokasi toko di peta.',
            'latitude.between'   => 'Koordinat tidak valid.',
            'longitude.between'  => 'Koordinat tidak valid.',
        ]);

        $data = [
            'store_name'        => $this->store_name,
            'address'           => $this->address,
            'category'          => $this->category,
            'latitude'          => (float) $this->latitude,
            'longitude'         => (float) $this->longitude,
            'operational_hours' => $this->operational_hours,
        ];

        // Ganti foto jika ada upload baru
        if ($this->store_photo) {
            // Hapus foto lama
            if ($this->currentPhoto) {
                Storage::disk('public')->delete($this->currentPhoto);
            }
            $data['photo'] = $this->store_photo->store('tenants/photos', 'public');
            $this->currentPhoto = $data['photo'];
            $this->store_photo  = null;
        }

        $this->tenant->update($data);
        $this->successMessage = 'Pengaturan toko berhasil disimpan.';
    }

    public function toggleOpen()
    {
        $this->tenant->update(['is_open' => !$this->tenant->is_open]);
        $this->tenant->refresh();
        $this->is_open = $this->tenant->is_open;
    }

    public function render()
    {
        return view('livewire.tenant.store-settings')
            ->layout('layouts.app', ['title' => 'Pengaturan Toko']);
    }
}