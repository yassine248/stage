<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTerrainRequest;
use App\Http\Requests\UpdateTerrainRequest;
use App\Http\Resources\TerrainResource;
use App\Models\Terrain;
use Illuminate\Http\Response;

class TerrainController extends Controller
{
    /**
     * Display a listing of all terrains with optional filters
     */
    public function index()
    {
        try {
            $terrains = Terrain::with('reservations')
                ->when(request('available'), function ($query) {
                    return $query->where('is_available', true);
                })
                ->when(request('type'), function ($query) {
                    return $query->where('type', request('type'));
                })
                ->paginate(15);

            return TerrainResource::collection($terrains);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la récupération des terrains',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created terrain
     */
    public function store(StoreTerrainRequest $request)
    {
        try {
            $terrain = Terrain::create($request->validated());
            return response()->json(new TerrainResource($terrain), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la création du terrain',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified terrain with its reservations
     */
    public function show(Terrain $terrain)
    {
        try {
            return new TerrainResource($terrain->load('reservations'));
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terrain introuvable',
                'error' => $e->getMessage()
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Update the specified terrain
     */
    public function update(UpdateTerrainRequest $request, Terrain $terrain)
    {
        try {
            $terrain->update($request->validated());

            return new TerrainResource($terrain);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la mise à jour du terrain',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified terrain
     */
    public function destroy(Terrain $terrain)
    {
        try {
            // Vérifier s'il y a des réservations actives
            if ($terrain->reservations()->where('status', '!=', 'cancelled')->exists()) {
                return response()->json([
                    'message' => 'Impossible de supprimer un terrain avec des réservations actives'
                ], Response::HTTP_CONFLICT);
            }

            $terrain->delete();

            return response()->noContent();
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la suppression du terrain',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
