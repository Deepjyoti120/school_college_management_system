<?php

use App\Http\Controllers\Api\V1\AcademicYearsController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\HolidayController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\WebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::prefix('v1')->group(function () {
    // Route::post('/otp-request', [AuthController::class, 'otpRequest']);
    // Route::post('/login', [AuthController::class, 'login']);
    Route::post('/login', [AuthController::class, 'emailLogin']);
    Route::get('/refresh', [AuthController::class, 'refresh']);
    // Route::post('webhook-razorpay', WebhookController::class);
    Route::middleware(['jwt.auth'])->group(function () {
        Route::get('/me', [UserController::class, 'me']);
        //Route::get('/users', [AuthController::class, 'me']);
        Route::get('academic-years', [AcademicYearsController::class, 'academicYears']);
        Route::get('payments', [PaymentController::class, 'pendingPayments']);
        Route::post('payments/history', [PaymentController::class, 'paymentsHistory']);
        Route::get('payment/init', [PaymentController::class, 'paymentInit']);
        Route::post('payment/success', [PaymentController::class, 'paymentSuccess']);
        Route::post('payment/failed', [PaymentController::class, 'paymentFailed']);
        // Holiday Start
        Route::get('holiday/list', [HolidayController::class, 'index']);
        // Holiday End
    });
});
// Auth::attempt(['email' => 'deepjyoti120281@gmail.com', 'password' => 'secret1234'])
