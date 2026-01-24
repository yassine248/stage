<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    protected $fillable = [
        'user_id',
        'terrain_id',
        'date',
        'start_time',
        'end_time',
        'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function terrain()
    {
        return $this->belongsTo(Terrain::class);
    }
}