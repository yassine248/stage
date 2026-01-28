<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\TerrainController;
use App\Http\Controllers\Api\ReservationController;
use App\Http\Controllers\Api\AvailabilityController;

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth:sanctum')->get('/me', function (Request $request) {
    return $request->user();
});

// Terrains routes - Full CRUD with authentication
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('terrains', TerrainController::class);
});

// Reservations routes - Full CRUD with authentication
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('reservations', ReservationController::class);
});

// Availability check
Route::middleware('auth:sanctum')->post('/terrains/{terrain}/check-availability', [AvailabilityController::class, 'check']);