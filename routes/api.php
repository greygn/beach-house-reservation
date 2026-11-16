<?php

use App\Http\Controllers\AddonController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\HouseController;
use App\Http\Controllers\HouseTypeController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\StatusController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/houses', [HouseController::class, 'index']);
Route::get('/houses/{house}', [HouseController::class, 'show']);
Route::get('/houses/{house}/reviews', [ReviewController::class, 'indexByHouse']); // only published
Route::get('/house-types', [HouseTypeController::class, 'index']);
Route::get('/addons', [AddonController::class, 'index']);
Route::get('/services', [ServiceController::class, 'index']);

// Authenticated user
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', fn (Request $request) => $request->user());

    Route::get('/bookings', [BookingController::class, 'index']);
    Route::post('/bookings', [BookingController::class, 'store']);
    Route::get('/bookings/{booking}', [BookingController::class, 'show']);
    Route::patch('/bookings/{booking}/cancel', [BookingController::class, 'cancel']); 

    Route::get('/addons/availability', [AddonController::class, 'availability']);

    Route::post('/bookings/{booking}/pay', [BookingController::class, 'pay']);
    Route::post('/bookings/{booking}/review', [ReviewController::class, 'store']);
});

// Staff only
Route::middleware(['auth:sanctum', 'staff'])->get('/staff/schedule', [StaffController::class, 'schedule']);

// Admin only
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::apiResource('users', UserController::class)->except(['store']);

    Route::apiResource('houses', HouseController::class)->except(['index', 'show']);
    Route::apiResource('house-types', HouseTypeController::class)->except(['index']);
    Route::apiResource('addons', AddonController::class)->except(['index']); 
    Route::apiResource('statuses', StatusController::class);
    Route::apiResource('services', ServiceController::class)->except(['index']);

    Route::get('/staff', [StaffController::class, 'index']);
    Route::post('/staff', [StaffController::class, 'store']);
    Route::post('/shifts', [ShiftController::class, 'store']);
    Route::delete('/shifts/{shift}', [ShiftController::class, 'destroy']);

    Route::get('/bookings', [BookingController::class, 'adminIndex']);
    Route::patch('/bookings/{booking}/approve', [BookingController::class, 'approve']);
    Route::patch('/bookings/{booking}/cancel', [BookingController::class, 'cancel']); 

    Route::get('/reviews', [ReviewController::class, 'index']);
    Route::patch('/reviews/{review}/status', [ReviewController::class, 'updateStatus']);
});
