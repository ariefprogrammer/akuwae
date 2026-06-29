<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string',
            'pin'          => 'required|digits:6',
        ]);

        $user = User::where('phone_number', $request->phone_number)
                    ->where('status', 'active')
                    ->first();

        if (!$user || !Hash::check($request->pin, $user->pin)) {
            return back()->withErrors([
                'phone_number' => 'Nomor HP atau PIN salah.',
            ])->withInput();
        }

        // Blokir admin login lewat sini
        if ($user->role === 'admin') {
            return back()->withErrors([
                'phone_number' => 'Akun admin tidak dapat login di sini.',
            ]);
        }

        auth()->login($user, $request->boolean('remember'));

        return redirect($this->redirectByRole($user->role));
    }

    public function logout(Request $request)
    {
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function redirectByRole(string $role): string
    {
        return match($role) {
            'customer' => route('customer.dashboard'),
            'driver'   => route('driver.dashboard'),
            'tenant'   => route('tenant.dashboard'),
            default    => '/',
        };
    }
}