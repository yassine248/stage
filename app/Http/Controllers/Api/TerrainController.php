<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TerrainController extends Controller
{
    public function index()
    {
        return Terrain::all();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'type' => 'required|string',
            'location' => 'required|string',
            'price_per_hour' => 'required|numeric',
        ]);

        return Terrain::create($data);
    }

    public function show(Terrain $terrain)
    {
        return $terrain;
    }

    public function update(Request $request, Terrain $terrain)
    {
        $terrain->update($request->all());
        return $terrain;
    }

    public function destroy(Terrain $terrain)
    {
        $terrain->delete();
        return response()->noContent();
    }
}
