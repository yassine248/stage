<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Terrain;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    // GET /api/reservations
    public function index(Request $request)
    {
        $reservations = Reservation::with('terrain')
            ->where('user_id', $request->user()->id)
            ->get();

        return response()->json($reservations);
    }

    // POST /api/reservations
    public function store(Request $request)
    {
        $request->validate([
            'terrain_id' => 'required|exists:terrains,id',
            'date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required',
        ]);

        // Vérification disponibilité
        $conflict = Reservation::where('terrain_id', $request->terrain_id)
            ->where('date', $request->date)
            ->where(function ($q) use ($request) {
                $q->whereBetween('start_time', [$request->start_time, $request->end_time])
                  ->orWhereBetween('end_time', [$request->start_time, $request->end_time])
                  ->orWhere(function ($q2) use ($request) {
                      $q2->where('start_time', '<=', $request->start_time)
                         ->where('end_time', '>=', $request->end_time);
                  });
            })
            ->exists();

        if ($conflict) {
            return response()->json([
                'message' => 'Terrain non disponible pour ce créneau'
            ], 409);
        }

        $reservation = Reservation::create([
            'user_id' => $request->user()->id,
            'terrain_id' => $request->terrain_id,
            'date' => $request->date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'status' => 'confirmed'
        ]);

        return response()->json($reservation, 201);
    }

    // DELETE /api/reservations/{id}
    public function destroy(Request $request, $id)
    {
        $reservation = Reservation::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $reservation->delete();

        return response()->json(['message' => 'Réservation annulée']);
    }
}


