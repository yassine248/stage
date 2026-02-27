<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTerrainRequest;
use App\Http\Requests\UpdateTerrainRequest;
use App\Http\Resources\TerrainResource;
use App\Models\Terrain;
use Illuminate\Http\Response;

class TerrainController extends Controller
{
    public function __construct()
    {
        $this->middleware(\App\Http\Middleware\AdminMiddleware::class);
    }
    public function index()
    {
        $terrains = Terrain::paginate(20);
        return TerrainResource::collection($terrains);
    }

    public function store(StoreTerrainRequest $request)
    {
        $terrain = Terrain::create($request->validated());
        return response()->json(new TerrainResource($terrain), Response::HTTP_CREATED);
    }

    public function show(Terrain $terrain)
    {
        return new TerrainResource($terrain->load('reservations'));
    }

    public function update(UpdateTerrainRequest $request, Terrain $terrain)
    {
        $terrain->update($request->validated());
        return new TerrainResource($terrain);
    }

    public function destroy(Terrain $terrain)
    {
        if ($terrain->reservations()->where('status', '!=', 'cancelled')->exists()) {
            return response()->json([
                'message' => 'Impossible de supprimer un terrain avec des réservations actives'
            ], Response::HTTP_CONFLICT);
        }

        $terrain->delete();
        return response()->noContent();
    }
}
