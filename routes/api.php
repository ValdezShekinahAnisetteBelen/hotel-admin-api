<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ReportController;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\ContactController;

/*
|--------------------------------------------------------------------------
| Email Verification Routes
|--------------------------------------------------------------------------
*/

Route::post('/contact', [ContactController::class, 'send']);

Route::get('/email/verify/{id}/{hash}', function ($id, $hash, Request $request) {
    // 1️⃣ Find the user by ID
    $user = User::find($id);

    if (!$user) {
        return response()->json(['message' => 'User not found.'], 404);
    }

    // 2️⃣ Validate the signed URL (prevents tampering)
    if (! URL::hasValidSignature($request)) {
        return response()->json(['message' => 'Invalid or expired verification link.'], 403);
    }

    // 3️⃣ Check if already verified
    if ($user->hasVerifiedEmail()) {
        return response()->json(['message' => 'Email already verified.']);
    }

    // 4️⃣ Mark email as verified
    $user->markEmailAsVerified();
    event(new Verified($user));

    return redirect(env('FRONTEND_URL') . '/login?verified=success');
})->name('verification.verify');


// ✅ Resend verification email
Route::post('/email/verification-notification', function (Request $request) {
    if ($request->user()->hasVerifiedEmail()) {
        return response()->json(['message' => 'Email already verified.']);
    }

    $request->user()->sendEmailVerificationNotification();

    return response()->json(['message' => 'Verification email resent.']);
})->middleware(['auth:sanctum', 'throttle:6,1'])->name('verification.send');

/*
|--------------------------------------------------------------------------
| Auth Routes
|--------------------------------------------------------------------------
*/
Route::post('/signup', [AuthController::class, 'signup']);
Route::post('/login', [AuthController::class, 'login']);

/*
|--------------------------------------------------------------------------
| Room Routes
|--------------------------------------------------------------------------
*/
Route::get('/rooms/featured', [RoomController::class, 'featured']);
Route::get('/rooms/discounted', [RoomController::class, 'discounted']);
Route::apiResource('rooms', RoomController::class);
Route::get('/rooms/{room}', [RoomController::class, 'show']);
Route::put('/rooms/{room}', [RoomController::class, 'update']);
Route::delete('/rooms/{room}', [RoomController::class, 'destroy']);
Route::post('/rooms', [RoomController::class, 'store']);

/*
|--------------------------------------------------------------------------
| Booking Routes
|--------------------------------------------------------------------------
*/
Route::post('/bookings', [BookingController::class, 'store']);
Route::get('/my-bookings', [BookingController::class, 'myBookings']);
Route::get('/bookings', [BookingController::class, 'allBookings']);
Route::put('/bookings/{id}/status', [BookingController::class, 'updateStatus']);
Route::put('/bookings/{id}/cancel', [BookingController::class, 'cancel']);
Route::get('/bookings/user', [BookingController::class, 'userBookings']);

/*
|--------------------------------------------------------------------------
| Admin & User Management
|--------------------------------------------------------------------------
*/
Route::get('/admin/profile/{id}', [UserController::class, 'show']);
Route::match(['put', 'post'], '/admin/profile/{id}', [UserController::class, 'update']);
Route::get('/users', [UserController::class, 'index']);
Route::get('/reports', [RoomController::class, 'bookingReports']);
