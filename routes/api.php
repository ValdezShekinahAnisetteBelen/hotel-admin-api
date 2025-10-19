<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\BookingController;

Route::post('/bookings', [BookingController::class, 'store']);

Route::post('/signup', [AuthController::class, 'signup']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/rooms', [RoomController::class, 'index']);
Route::post('/rooms', [RoomController::class, 'store']);
Route::put('/rooms/{room}', [RoomController::class, 'update']);
Route::delete('/rooms/{room}', [RoomController::class, 'destroy']);  // ADD THIS
Route::get('/rooms/{room}', [RoomController::class, 'show']);  // ADD THIS

    Route::post('/bookings', [BookingController::class, 'store']);
    Route::get('/my-bookings', [BookingController::class, 'myBookings']);

    // Admin routes
    Route::get('/bookings', [BookingController::class, 'allBookings']);
    Route::put('/bookings/{id}/status', [BookingController::class, 'updateStatus']);
        Route::put('/bookings/{id}/cancel', [BookingController::class, 'cancel']);

Route::get('/bookings/user', [BookingController::class, 'userBookings']);
