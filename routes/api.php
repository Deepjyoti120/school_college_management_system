<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::prefix('v1')->group(function () {
    // Route::post('/otp-request', [AuthController::class, 'otpRequest']);
    // Route::post('/login', [AuthController::class, 'login']);
    Route::post('/login', [AuthController::class, 'emailLogin']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::middleware(['jwt.auth'])->group(function () {
        Route::get('/me', [UserController::class, 'me']);
        Route::get('/users', [AuthController::class, 'me']);
        Route::get('/payments', [PaymentController::class, 'pendingPayments']);
    });
});
// Auth::attempt(['email' => 'deepjyoti120281@gmail.com', 'password' => 'secret1234'])
