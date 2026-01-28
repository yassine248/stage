<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReservationRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'terrain_id' => 'required|exists:terrains,id',
            'date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ];
    }

    public function messages()
    {
        return [
            'terrain_id.required' => 'Le terrain est obligatoire',
            'terrain_id.exists' => 'Ce terrain n\'existe pas',
            'date.required' => 'La date est obligatoire',
            'date.after_or_equal' => 'La date doit être aujourd\'hui ou dans le futur',
            'start_time.required' => 'L\'heure de début est obligatoire',
            'end_time.required' => 'L\'heure de fin est obligatoire',
            'end_time.after' => 'L\'heure de fin doit être après l\'heure de début',
        ];
    }
}
