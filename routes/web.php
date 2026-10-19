<?php

use App\Http\Controllers\Web\AdminController;
use App\Http\Controllers\Web\SiteController;
use Illuminate\Support\Facades\Route;

Route::get('/', [SiteController::class, 'houses'])->name('houses');
Route::get('/houses/{house}', [SiteController::class, 'house'])->name('houses.show');

Route::middleware('auth')->get('/bookings', [SiteController::class, 'bookings'])->name('bookings');

Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/', [AdminController::class, 'bookings'])->name('admin.bookings');
    Route::get('/reviews', [AdminController::class, 'reviews'])->name('admin.reviews');
    Route::get('/houses', [AdminController::class, 'houses'])->name('admin.houses');
    Route::get('/house-types', [AdminController::class, 'houseTypes'])->name('admin.house-types');
    Route::get('/addons', [AdminController::class, 'addons'])->name('admin.addons');
    Route::get('/statuses', [AdminController::class, 'statuses'])->name('admin.statuses');
    Route::get('/users', [AdminController::class, 'users'])->name('admin.users');
});

require __DIR__.'/auth.php';
