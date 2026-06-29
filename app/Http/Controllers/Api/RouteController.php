<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\OrsService;
use Illuminate\Http\Request;

class RouteController extends Controller
{
    public function calculate(Request $request, OrsService $ors)
    {
        $request->validate([
            'origin_lat' => 'required|numeric',
            'origin_lng' => 'required|numeric',
            'dest_lat'   => 'required|numeric',
            'dest_lng'   => 'required|numeric',
            'profile'    => 'nullable|string|in:driving-car,cycling-regular,foot-walking',
        ]);

        $profile = $request->input('profile', 'driving-car');

        $route = $ors->getRoute(
            $request->origin_lat,
            $request->origin_lng,
            $request->dest_lat,
            $request->dest_lng,
            $profile
        );

        return response()->json($route);
    }
}