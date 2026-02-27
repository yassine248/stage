<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\TimeSlot;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class TimeSlotController extends Controller
{
    public function __construct()
    {
        $this->middleware(\App\Http\Middleware\AdminMiddleware::class);
    }
    public function index()
    {
        return response()->json(TimeSlot::with('terrain')->paginate(25));
    }

    public function store(\App\Http\Requests\StoreTimeSlotRequest $request)
    {
        $terrainId = $request->route('id');
       
         $day = $date = now()->dayOfWeek;
         $days = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'];
        $timeslot = TimeSlot::create([
            'terrain_id' => $terrainId,
            'day' =>  $days[$day],
            'start_time' => $request->input('start_time'),
            'end_time' => $request->input('end_time'),
        ]);
        return response()->json($timeslot, Response::HTTP_CREATED);
    }

    public function show(TimeSlot $timeslot)
    {
        return response()->json($timeslot->load('terrain'));
    }

    public function update(\App\Http\Requests\UpdateTimeSlotRequest $request, TimeSlot $timeslot)
    {
        $timeslot->update($request->validated());
        return response()->json($timeslot);
    }

    public function destroy(TimeSlot $timeslot)
    {
        $timeslot->delete();
        return response()->noContent();
    }
}
