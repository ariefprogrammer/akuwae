<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    // Step 1: Kirim OTP ke nomor HP
    public function sendOtp(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string|max:20|unique:users,phone_number',
            'role'         => 'required|in:customer,driver,tenant',
        ]);

        $otp = rand(100000, 999999);
        $key = 'otp_' . $request->phone_number;

        // Simpan OTP + role di cache selama 5 menit
        Cache::put($key, [
            'otp'  => $otp,
            'role' => $request->role,
        ], now()->addMinutes(5));

        // Kirim OTP via Fonnte (WhatsApp)
        $this->sendWhatsapp($request->phone_number, "Kode OTP ToLong App kamu: *$otp*\nBerlaku 5 menit. Jangan bagikan ke siapapun.");

        return response()->json([
            'message' => 'OTP berhasil dikirim.',
        ]);
    }

    // Step 2: Verifikasi OTP
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string',
            'otp'          => 'required|digits:6',
        ]);

        $key  = 'otp_' . $request->phone_number;
        $data = Cache::get($key);

        if (!$data || $data['otp'] != $request->otp) {
            return response()->json(['message' => 'OTP tidak valid atau sudah kadaluarsa.'], 422);
        }

        // OTP valid — simpan token sementara untuk step set PIN
        $token = Str::random(40);
        Cache::put('reg_token_' . $request->phone_number, [
            'token' => $token,
            'role'  => $data['role'],
        ], now()->addMinutes(10));

        // Hapus OTP dari cache
        Cache::forget($key);

        return response()->json([
            'message' => 'OTP valid.',
            'token'   => $token,
        ]);
    }

    // Step 3: Set PIN & buat akun
    public function setPin(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string',
            'pin'          => 'required|digits:6|confirmed',
            'name'         => 'required|string|max:100',
            'token'        => 'required|string',
        ]);

        $key  = 'reg_token_' . $request->phone_number;
        $data = Cache::get($key);

        if (!$data || $data['token'] !== $request->token) {
            return response()->json(['message' => 'Sesi registrasi tidak valid. Ulangi dari awal.'], 422);
        }

        // Buat user
        $user = User::create([
            'phone_number' => $request->phone_number,
            'pin'          => Hash::make($request->pin),
            'role'         => $data['role'],
            'status'       => 'active',
        ]);

        // Buat profil sesuai role
        if ($data['role'] === 'customer') {
            Customer::create([
                'user_id' => $user->id,
                'name'    => $request->name,
            ]);
        }
        // Driver & Tenant dibuat saat mereka isi form verifikasi — bukan di sini

        // Hapus token registrasi
        Cache::forget($key);

        // Auto login setelah registrasi
        auth()->login($user);

        return response()->json([
            'message'  => 'Registrasi berhasil.',
            'role'     => $user->role,
            'redirect' => $this->redirectByRole($user->role),
        ]);
    }

    // Helper: arahkan ke dashboard sesuai role
    private function redirectByRole(string $role): string
    {
        return match($role) {
            'customer' => route('customer.dashboard'),
            'driver'   => route('driver.dashboard'),
            'tenant'   => route('tenant.dashboard'),
            default    => '/',
        };
    }

    // Helper: kirim WhatsApp via Fonnte
    private function sendWhatsapp(string $phone, string $message): void
    {
        $token = config('services.fonnte.token');

        // Normalisasi nomor: 08xx → 628xx
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
}