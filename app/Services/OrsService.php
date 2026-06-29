<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OrsService
{
    private string $apiKey;
    private string $baseUrl = 'https://api.openrouteservice.org/v2/directions';

    public function __construct()
    {
        $this->apiKey = config('services.ors.api_key');
    }

    /**
     * Hitung jarak rute jalan sesungguhnya antara dua titik.
     * Return: ['distance_km' => float, 'duration_min' => float, 'geometry' => array|null]
     * Fallback ke Haversine jika ORS gagal.
     */
    public function getRoute(float $originLat, float $originLng, float $destLat, float $destLng, string $profile = 'driving-car'): array
    {
        try {
            $response = Http::timeout(5)->get("{$this->baseUrl}/{$profile}", [
                'api_key' => $this->apiKey,
                'start'   => "{$originLng},{$originLat}", // ORS pakai format lng,lat
                'end'     => "{$destLng},{$destLat}",
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $summary = $data['features'][0]['properties']['summary'] ?? null;
                $geometry = $data['features'][0]['geometry']['coordinates'] ?? null;

                if ($summary) {
                    return [
                        'distance_km'  => round($summary['distance'] / 1000, 2),
                        'duration_min' => round($summary['duration'] / 60, 1),
                        'geometry'     => $geometry, // array of [lng, lat] points
                        'source'       => 'ors',
                    ];
                }
            }

            Log::warning('ORS request failed', ['status' => $response->status(), 'body' => $response->body()]);
        } catch (\Exception $e) {
            Log::warning('ORS exception: ' . $e->getMessage());
        }

        // Fallback ke Haversine dengan faktor koreksi
        return $this->fallbackHaversine($originLat, $originLng, $destLat, $destLng);
    }

    private function fallbackHaversine(float $lat1, float $lon1, float $lat2, float $lon2): array
    {
        $R = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat/2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) ** 2;
        $distance = $R * 2 * atan2(sqrt($a), sqrt(1-$a));

        return [
            'distance_km'  => round($distance * 1.3, 2), // koreksi jalan
            'duration_min' => null,
            'geometry'     => null,
            'source'       => 'haversine_fallback',
        ];
    }
}