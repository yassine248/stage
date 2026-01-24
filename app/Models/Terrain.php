<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Terrain extends Model
{
        protected $fillable = [
        'name',
        'type',
        'location',
        'price_per_hour',
        'is_available',
    ];
    public function reservations()
{
    return $this->hasMany(Reservation::class);
}

}
