<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\TerrainController;
use App\Http\Controllers\Api\ReservationController;
use App\Http\Controllers\Api\AvailabilityController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\Admin\TerrainController as AdminTerrainController;
use App\Http\Controllers\Api\Admin\ReservationController as AdminReservationController;
use App\Http\Controllers\Api\Admin\TimeSlotController as AdminTimeSlotController;
use App\Http\Controllers\Api\Admin\StatisticsController as AdminStatisticsController;

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth:sanctum')->get('/me', function (Request $request) {
    return $request->user();
});

// Dashboard routes
Route::middleware('auth:sanctum')->prefix('dashboard')->group(function () {
    Route::get('/user', [DashboardController::class, 'userDashboard']);
    Route::get('/admin', [DashboardController::class, 'adminDashboard']);
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

// User-specific reservation helpers
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/reservations/history', [ReservationController::class, 'history']);
    Route::post('/reservations/{reservation}/cancel', [ReservationController::class, 'cancel']);
});

// Admin routes (prefix `admin`). Apply additional admin authorization middleware or policies as needed.
Route::middleware('auth:sanctum')->prefix('admin')->group(function () {
    Route::apiResource('terrains', AdminTerrainController::class);
    Route::apiResource('terrains/{id}/slots', AdminTimeSlotController::class);

    // Admin reservation management: list, validate, cancel
    Route::get('reservations', [AdminReservationController::class, 'index']);
    Route::put('reservations/{reservation}/validate', [AdminReservationController::class, 'validate']);
    Route::put('reservations/{reservation}/cancel', [AdminReservationController::class, 'cancel']);

    // Admin statistics
    Route::get('statistics', [AdminStatisticsController::class, 'index']);
});
