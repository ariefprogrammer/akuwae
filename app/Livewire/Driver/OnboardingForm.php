<?php

namespace App\Livewire\Driver;

use App\Models\Driver;
use App\Models\DriverDocument;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;

class OnboardingForm extends Component
{
    use WithFileUploads;

    public int    $step            = 1;

    // Step 1 — Data diri & kendaraan
    public string $name            = '';
    public string $vehicle_type    = 'motor';
    public string $vehicle_plate   = '';

    // Step 2 — Dokumen
    public string $ktp_number      = '';
    public string $sim_number      = '';
    public        $stnk_photo      = null;
    public        $selfie_ktp_photo = null;

    public function mount()
    {
        $driver = auth()->user()->driver;
        if ($driver) {
            return redirect()->route('driver.dashboard');
        }
    }

    // ── Step 1 ───────────────────────────────────────────────
    public function nextStep()
    {
        $this->validate([
            'name'          => 'required|string|max:100',
            'vehicle_type'  => 'required|in:motor,mobil',
            'vehicle_plate' => 'required|string|max:20',
        ], [
            'name.required'          => 'Nama lengkap wajib diisi.',
            'vehicle_plate.required' => 'Nomor plat kendaraan wajib diisi.',
        ]);

        $this->step = 2;
    }

    // ── Step 2 ───────────────────────────────────────────────
    public function save()
    {
        $this->validate([
            'ktp_number'       => 'required|digits:16',
            'sim_number'       => 'required|string|max:30',
            'stnk_photo'       => 'required|image|max:3072',
            'selfie_ktp_photo' => 'required|image|max:3072',
        ], [
            'ktp_number.digits'        => 'Nomor KTP harus 16 digit.',
            'stnk_photo.required'      => 'Foto STNK wajib diupload.',
            'selfie_ktp_photo.required'=> 'Foto selfie + KTP wajib diupload.',
        ]);

        // Simpan foto
        $stnkPath   = $this->stnk_photo->store('drivers/documents', 'public');
        $selfiePath = $this->selfie_ktp_photo->store('drivers/documents', 'public');

        // Buat profil driver
        $driver = Driver::create([
            'id'                  => Str::uuid(),
            'user_id'             => auth()->id(),
            'name'                => $this->name,
            'vehicle_type'        => $this->vehicle_type,
            'vehicle_plate'       => strtoupper($this->vehicle_plate),
            'verification_status' => 'pending',
            'is_online'           => false,
        ]);

        // Simpan dokumen
        DriverDocument::create([
            'driver_id'        => $driver->id,
            'ktp_number'       => $this->ktp_number,
            'sim_number'       => $this->sim_number,
            'stnk_photo'       => $stnkPath,
            'selfie_ktp_photo' => $selfiePath,
        ]);

        return redirect()->route('driver.dashboard');
    }

    public function render()
    {
        return view('livewire.driver.onboarding-form')
            ->layout('layouts.app', ['title' => 'Daftar Mitra Driver']);
    }
}