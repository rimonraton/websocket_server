<?php

use App\Http\Controllers\Api\RideController;
use App\Http\Controllers\Api\TestController;
use Illuminate\Support\Facades\Route;

// Test endpoints
Route::post('/test/counter', [TestController::class, 'incrementCounter']);

// Ride management endpoints
Route::post('/rides', [RideController::class, 'store']);
Route::get('/rides/{rideId}', [RideController::class, 'show']);
Route::post('/rides/{rideId}/accept', [RideController::class, 'accept']);
Route::post('/rides/{rideId}/pickup', [RideController::class, 'pickup']);
Route::post('/rides/{rideId}/complete', [RideController::class, 'complete']);
Route::post('/rides/{rideId}/position', [RideController::class, 'updatePosition']);
