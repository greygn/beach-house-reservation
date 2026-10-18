<?php

use App\Http\Controllers\AddonController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\HouseController;
use App\Http\Controllers\HouseTypeController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\StatusController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Login, registration, logout, password reset: Breeze, see routes/auth.php (web routes, session cookie).

Route::get('/houses', [HouseController::class, 'index']);
Route::get('/houses/{house}', [HouseController::class, 'show']);
Route::get('/houses/{house}/reviews', [ReviewController::class, 'indexByHouse']); // only published
Route::get('/house-types', [HouseTypeController::class, 'index']);
Route::get('/addons', [AddonController::class, 'index']);

// Authenticated user
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', fn (Request $request) => $request->user());

    // Own bookings
    Route::get('/bookings', [BookingController::class, 'index']);
    Route::post('/bookings', [BookingController::class, 'store']);
    Route::get('/bookings/{booking}', [BookingController::class, 'show']);
    Route::patch('/bookings/{booking}/cancel', [BookingController::class, 'cancel']); // own booking

    // Addon availability for a period: ?time_start=&time_end=
    Route::get('/addons/availability', [AddonController::class, 'availability']);

    // Review only tied to own booking
    Route::post('/bookings/{booking}/review', [ReviewController::class, 'store']);
});

// Admin only
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::apiResource('users', UserController::class)->except(['store']);

    Route::apiResource('houses', HouseController::class)->except(['index', 'show']);
    Route::apiResource('house-types', HouseTypeController::class)->except(['index']);
    Route::apiResource('addons', AddonController::class)->except(['index']); // update changes quantity
    Route::apiResource('statuses', StatusController::class);

    // All bookings; filters: ?house_id=&date_from=&date_to=&status=
    Route::get('/bookings', [BookingController::class, 'adminIndex']);
    Route::patch('/bookings/{booking}/cancel', [BookingController::class, 'cancel']); // status -> cancelled

    // Review moderation
    Route::get('/reviews', [ReviewController::class, 'index']);
    Route::patch('/reviews/{review}/status', [ReviewController::class, 'updateStatus']);
});
