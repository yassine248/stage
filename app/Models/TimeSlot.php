<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimeSlot extends Model
{
    protected $fillable = [
        'terrain_id',
        'day',
        'start_time',
        'end_time',
    ];

    public function terrain()
    {
        return $this->belongsTo(Terrain::class);
    }
}
