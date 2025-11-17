<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\ReservationController;

// ----------------------
// Event Routes
// ----------------------
Route::apiResource('events', EventController::class)->only(['index', 'show']);

Route::post('events/{event}/reserve', [ReservationController::class, 'reserve']);
Route::post('reservations/{reservation}/purchase', [ReservationController::class, 'purchase']);
