<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ReservationController extends Controller
{
    public function __construct()
    {
        $this->middleware(\App\Http\Middleware\AdminMiddleware::class);
    }
    public function index(Request $request)
    {
        $reservations = Reservation::with('terrain', 'user')
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->terrain_id, fn($q) => $q->where('terrain_id', $request->terrain_id))
            ->orderBy('date', 'desc')
            ->paginate(25);

        return response()->json($reservations);
    }

    public function validate(Request $request, Reservation $reservation)
    {
        $reservation->update(['status' => 'confirmed']);
        return response()->json(['message' => 'Réservation validée', 'reservation' => $reservation]);
    }

    public function cancel(Request $request, Reservation $reservation)
    {
        $reservation->update(['status' => 'cancelled']);
        return response()->json(['message' => 'Réservation annulée', 'reservation' => $reservation]);
    }
}
