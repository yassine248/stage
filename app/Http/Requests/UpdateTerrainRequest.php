<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTerrainRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'sometimes|string|max:255|unique:terrains,name,' . $this->terrain->id,
            'type' => 'sometimes|string|max:100',
            'location' => 'sometimes|string|max:500',
            'price_per_hour' => 'sometimes|numeric|min:0',
            'is_available' => 'nullable|boolean',
            'description' => 'nullable|string',
            'capacity' => 'nullable|integer|min:1',
        ];
    }

    public function messages()
    {
        return [
            'name.unique' => 'Ce nom de terrain existe déjà',
            'price_per_hour.min' => 'Le prix doit être positif',
        ];
    }
}
