<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TerrainResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'location' => $this->location,
            'description' => $this->description,
            'price_per_hour' => $this->price_per_hour,
            'capacity' => $this->capacity,
            'is_available' => $this->is_available,
            'reservations_count' => $this->whenLoaded('reservations', $this->reservations->count()),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
