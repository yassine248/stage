<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReservationRequest;
use App\Http\Resources\ReservationResource;
use App\Models\Reservation;
use App\Models\Terrain;
use Illuminate\Http\Response;

class ReservationController extends Controller
{
    /**
     * Display a listing of user reservations with optional filters
     */
    public function index()
    {
        try {
            $reservations = Reservation::with('terrain', 'user')
                ->where('user_id', auth()->id())
                ->when(request('status'), function ($query) {
                    return $query->where('status', request('status'));
                })
                ->when(request('terrain_id'), function ($query) {
                    return $query->where('terrain_id', request('terrain_id'));
                })
                ->orderBy('date', 'asc')
                ->paginate(15);

            return ReservationResource::collection($reservations);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la récupération des réservations',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created reservation
     */
    public function store(StoreReservationRequest $request)
    {
        try {
            $terrain = Terrain::findOrFail($request->terrain_id);

            // Vérifier la disponibilité
            $conflict = Reservation::where('terrain_id', $request->terrain_id)
                ->where('date', $request->date)
                ->where('status', '!=', 'cancelled')
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
                ], Response::HTTP_CONFLICT);
            }

            $reservation = Reservation::create([
                'user_id' => auth()->id(),
                'terrain_id' => $request->terrain_id,
                'date' => $request->date,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'status' => 'confirmed'
            ]);

            return response()->json(new ReservationResource($reservation->load('terrain', 'user')), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la création de la réservation',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified reservation
     */
    public function show(Reservation $reservation)
    {
        try {
            // Vérifier que l'utilisateur est propriétaire de la réservation
            if ($reservation->user_id !== auth()->id()) {
                return response()->json([
                    'message' => 'Non autorisé'
                ], Response::HTTP_FORBIDDEN);
            }

            return new ReservationResource($reservation->load('terrain', 'user'));
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Réservation introuvable',
                'error' => $e->getMessage()
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Update the specified reservation
     */
    public function update(\Illuminate\Http\Request $request, Reservation $reservation)
    {
        try {
            // Vérifier que l'utilisateur est propriétaire de la réservation
            if ($reservation->user_id !== auth()->id()) {
                return response()->json([
                    'message' => 'Non autorisé'
                ], Response::HTTP_FORBIDDEN);
            }

            // Vérifier que la réservation n'est pas déjà annulée
            if ($reservation->status === 'cancelled') {
                return response()->json([
                    'message' => 'Impossible de modifier une réservation annulée'
                ], Response::HTTP_CONFLICT);
            }

            $validated = $request->validate([
                'date' => 'sometimes|date|after_or_equal:today',
                'start_time' => 'sometimes|date_format:H:i',
                'end_time' => 'sometimes|date_format:H:i',
                'status' => 'sometimes|in:confirmed,pending,cancelled',
            ]);

            // Si la date ou les heures changent, vérifier la disponibilité
            if (isset($validated['date']) || isset($validated['start_time']) || isset($validated['end_time'])) {
                $date = $validated['date'] ?? $reservation->date;
                $startTime = $validated['start_time'] ?? $reservation->start_time;
                $endTime = $validated['end_time'] ?? $reservation->end_time;

                $conflict = Reservation::where('terrain_id', $reservation->terrain_id)
                    ->where('id', '!=', $reservation->id)
                    ->where('date', $date)
                    ->where('status', '!=', 'cancelled')
                    ->where(function ($q) use ($startTime, $endTime) {
                        $q->whereBetween('start_time', [$startTime, $endTime])
                          ->orWhereBetween('end_time', [$startTime, $endTime])
                          ->orWhere(function ($q2) use ($startTime, $endTime) {
                              $q2->where('start_time', '<=', $startTime)
                                 ->where('end_time', '>=', $endTime);
                          });
                    })
                    ->exists();

                if ($conflict) {
                    return response()->json([
                        'message' => 'Terrain non disponible pour ce créneau'
                    ], Response::HTTP_CONFLICT);
                }
            }

            $reservation->update($validated);

            return new ReservationResource($reservation->load('terrain', 'user'));
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la mise à jour de la réservation',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified reservation
     */
    public function destroy(Reservation $reservation)
    {
        try {
            // Vérifier que l'utilisateur est propriétaire de la réservation
            if ($reservation->user_id !== auth()->id()) {
                return response()->json([
                    'message' => 'Non autorisé'
                ], Response::HTTP_FORBIDDEN);
            }

            // Marquer la réservation comme annulée au lieu de la supprimer
            $reservation->update(['status' => 'cancelled']);

            return response()->json([
                'message' => 'Réservation annulée avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de l\'annulation de la réservation',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}


