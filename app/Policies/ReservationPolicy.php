<?php

namespace App\Policies;

use App\Models\Reservation;
use App\Models\User;

class ReservationPolicy
{
    public function view(User $user, Reservation $reservation)
    {
        return $user->id === $reservation->user_id || ($user->is_admin ?? false);
    }

    public function update(User $user, Reservation $reservation)
    {
        return $user->id === $reservation->user_id && $reservation->status !== 'cancelled';
    }

    public function delete(User $user, Reservation $reservation)
    {
        return $user->id === $reservation->user_id;
    }

    public function cancel(User $user, Reservation $reservation)
    {
        return $user->id === $reservation->user_id || ($user->is_admin ?? false);
    }
}
