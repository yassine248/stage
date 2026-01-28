<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTerrainRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255|unique:terrains,name',
            'type' => 'required|string|max:100',
            'location' => 'required|string|max:500',
            'price_per_hour' => 'required|numeric|min:0',
            'is_available' => 'nullable|boolean',
            'description' => 'nullable|string',
            'capacity' => 'nullable|integer|min:1',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Le nom du terrain est obligatoire',
            'name.unique' => 'Ce nom de terrain existe déjà',
            'type.required' => 'Le type de terrain est obligatoire',
            'location.required' => 'La localisation est obligatoire',
            'price_per_hour.required' => 'Le prix par heure est obligatoire',
            'price_per_hour.min' => 'Le prix doit être positif',
        ];
    }
}
