<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CleanSocketIdHeader
{
    public function handle(Request $request, Closure $next)
    {
        $socketId = $request->header('X-Socket-Id');

        // Hapus header jika kosong, "undefined", atau format tidak valid
        if (!$socketId || $socketId === 'undefined' || !preg_match('/^\d+\.\d+$/', $socketId)) {
            $request->headers->remove('X-Socket-Id');
        }

        return $next($request);
    }
}