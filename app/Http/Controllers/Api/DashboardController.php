<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use Illuminate\Http\Response;

class DashboardController extends Controller
{
    /**
     * Get user dashboard data
     */
    public function userDashboard()
    {
        try {
            $userId = auth()->id();

            $reservations = Reservation::where('user_id', $userId)
                ->selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->get()
                ->pluck('count', 'status')
                ->toArray();

            // Ensure all statuses are present, even if count is 0
            $statuses = ['confirmed', 'pending', 'cancelled'];
            $data = [];
            foreach ($statuses as $status) {
                $data[$status] = $reservations[$status] ?? 0;
            }

            // Also return the user's reservation list with translated status labels
            $statusMap = [
                'confirmed' => 'confirmée',
                'cancelled' => 'annulée',
                'pending' => 'en attente'
            ];

            $userReservations = Reservation::with('terrain')
                ->where('user_id', $userId)
                ->orderBy('date', 'desc')
                ->get()
                ->map(function ($r) use ($statusMap) {
                    return [
                        'id' => $r->id,
                        'date' => $r->date,
                        'start_time' => $r->start_time,
                        'end_time' => $r->end_time,
                        'terrain' => $r->terrain ? $r->terrain->name : null,
                        'statut' => $statusMap[$r->status] ?? $r->status
                    ];
                });

            return response()->json([
                'mes_reservations' => $data,
                'reservations' => $userReservations
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la récupération du tableau de bord utilisateur',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get admin dashboard data
     */
    public function adminDashboard()
    {
        try {
            // Total reservations
            $totalReservations = Reservation::count();

            // Reservations by terrain
            $reservationsByTerrain = Reservation::with('terrain')
                ->selectRaw('terrain_id, COUNT(*) as count')
                ->groupBy('terrain_id')
                ->get()
                ->map(function ($item) {
                    return [
                        'terrain' => $item->terrain->name,
                        'count' => $item->count
                    ];
                });

            // Reservations by date
            $reservationsByDate = Reservation::selectRaw('date, COUNT(*) as count')
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->map(function ($item) {
                    return [
                        'date' => $item->date,
                        'count' => $item->count
                    ];
                });

            return response()->json([
                'total_reservations' => $totalReservations,
                'reservations_par_terrain' => $reservationsByTerrain,
                'reservations_par_date' => $reservationsByDate
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la récupération du tableau de bord administrateur',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
