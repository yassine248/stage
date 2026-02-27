<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Terrain;
use Illuminate\Http\Response;

class StatisticsController extends Controller
{
    public function __construct()
    {
        $this->middleware(\App\Http\Middleware\AdminMiddleware::class);
    }
    public function index()
    {
        $totalTerrains = Terrain::count();
        $totalReservations = Reservation::count();
        $activeReservations = Reservation::where('status', '!=', 'cancelled')->count();

        // Estimate revenue from confirmed reservations
        $revenue = Reservation::where('status', 'confirmed')
            ->join('terrains', 'reservations.terrain_id', '=', 'terrains.id')
            ->selectRaw("SUM((TIME_TO_SEC(TIMEDIFF(end_time, start_time))/3600) * terrains.price_per_hour) as revenue")
            ->value('revenue') ?? 0;

        return response()->json([
            'total_terrains' => $totalTerrains,
            'total_reservations' => $totalReservations,
            'active_reservations' => $activeReservations,
            'estimated_revenue' => (float) $revenue,
        ], Response::HTTP_OK);
    }
}
