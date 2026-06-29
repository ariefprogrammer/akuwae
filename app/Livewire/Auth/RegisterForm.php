<?php

namespace App\Livewire\Auth;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Component;

class RegisterForm extends Component
{
    public int    $step         = 1;
    public string $phone_number = '';
    public string $role         = 'customer';
    public string $otp          = '';
    public string $name         = '';
    public string $pin          = '';
    public string $pin_confirmation = '';
    public string $reg_token    = '';
    public string $error        = '';
    public string $success      = '';

    // ============================================================
    // STEP 1 — Kirim OTP
    // ============================================================
    public function sendOtp()
    {
        $this->error = '';

        $this->validate([
            'phone_number' => 'required|string|max:20|unique:users,phone_number',
            'role'         => 'required|in:customer,driver,tenant',
        ], [
            'phone_number.unique' => 'Nomor HP ini sudah terdaftar.',
        ]);

        $otp = rand(100000, 999999);
        $key = 'otp_' . $this->phone_number;

        Cache::put($key, [
            'otp'  => $otp,
            'role' => $this->role,
        ], now()->addMinutes(5));

        $this->sendWhatsapp(
            $this->phone_number,
            "Kode OTP Aku Wae App kamu: *$otp*\nBerlaku 5 menit. Jangan bagikan ke siapapun."
        );
        // if (app()->environment('local')) {
        //     session()->flash('dev_otp', "DEV MODE — OTP kamu: $otp");
        // }

        $this->success = 'OTP berhasil dikirim ke ' . $this->phone_number;
        $this->step    = 2;
    }

    // ============================================================
    // STEP 2 — Verifikasi OTP
    // ============================================================
    public function verifyOtp()
    {
        $this->error = '';

        $this->validate([
            'otp' => 'required|digits:6',
        ]);

        $key  = 'otp_' . $this->phone_number;
        $data = Cache::get($key);

        if (!$data || $data['otp'] != $this->otp) {
            $this->error = 'OTP tidak valid atau sudah kadaluarsa.';
            return;
        }

        // OTP valid — buat token sementara untuk step 3
        $token = Str::random(40);
        Cache::put('reg_token_' . $this->phone_number, [
            'token' => $token,
            'role'  => $data['role'],
        ], now()->addMinutes(10));

        Cache::forget($key);

        $this->reg_token = $token;
        $this->success   = 'Nomor HP terverifikasi!';
        $this->step      = 3;
    }

    // ============================================================
    // STEP 3 — Set nama + PIN, buat akun
    // ============================================================
    public function register()
    {
        $this->error = '';

        $this->validate([
            'name' => 'required|string|max:100',
            'pin'  => 'required|digits:6|same:pin_confirmation',
        ], [
            'pin.same' => 'Konfirmasi PIN tidak cocok.',
        ]);

        $key  = 'reg_token_' . $this->phone_number;
        $data = Cache::get($key);

        if (!$data || $data['token'] !== $this->reg_token) {
            $this->error = 'Sesi registrasi tidak valid. Ulangi dari awal.';
            $this->step  = 1;
            return;
        }

        // Buat user
        $user = User::create([
            'phone_number' => $this->phone_number,
            'pin'          => Hash::make($this->pin),
            'role'         => $data['role'],
            'status'       => 'active',
        ]);

        // Buat profil customer
        // Driver & Tenant isi profil lengkap saat onboarding
        if ($data['role'] === 'customer') {
            Customer::create([
                'user_id' => $user->id,
                'name'    => $this->name,
            ]);
        }

        Cache::forget($key);

        auth()->login($user);

        return redirect()->to(match($user->role) {
            'customer' => route('customer.dashboard'),
            'driver'   => route('driver.dashboard'),
            'tenant'   => route('tenant.dashboard'),
            default    => '/',
        });
    }

    // ============================================================
    // HELPER — Kirim WhatsApp via Fonnte
    // ============================================================
    private function sendWhatsapp(string $phone, string $message): void
    {
        $token = config('services.fonnte.token');
        $phone = preg_replace('/^0/', '62', $phone);

        $ch = curl_init('https://api.fonnte.com/send');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => [
                'target'  => $phone,
                'message' => $message,
            ],
            CURLOPT_HTTPHEADER => [
                'Authorization: ' . $token,
            ],
        ]);
        curl_exec($ch);
        curl_close($ch);
    }

    public function render()
    {
        return view('livewire.auth.register-form')
            ->layout('layouts.auth', ['title' => 'Daftar']);
    }
}