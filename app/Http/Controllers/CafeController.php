<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CafeController extends Controller
{
    public function index(): View
    {
        return view('cafes.index');
    }

    public function nearby(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
            'radius' => ['nullable', 'integer', 'min:100', 'max:5000'],
        ]);

        $lat = (float) $validated['lat'];
        $lng = (float) $validated['lng'];
        $radius = (int) ($validated['radius'] ?? 1000);

        $cafes = DB::select(
            'SELECT
                id,
                name,
                address,
                description,
                ST_Y(location::geometry) AS latitude,
                ST_X(location::geometry) AS longitude,
                ROUND(
                    ST_Distance(
                        location,
                        ST_SetSRID(ST_MakePoint(?, ?), 4326)::geography
                    )::numeric
                ) AS distance_meters
             FROM cafes
             WHERE ST_DWithin(
                location,
                ST_SetSRID(ST_MakePoint(?, ?), 4326)::geography,
                ?
             )
             ORDER BY distance_meters ASC',
            [$lng, $lat, $lng, $lat, $radius]
        );

        return response()->json([
            'count' => count($cafes),
            'radius_meters' => $radius,
            'cafes' => $cafes,
        ]);
    }
}
