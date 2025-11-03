<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Users;
use App\Http\Controllers\Category;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FeeController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\Restaurent;

Route::get('/', function () {
    if (Auth::check()) {
       return redirect()->route('dashboard');
    }
    return Inertia::render('auth/Login');
})->name('home');

// Route::get('dashboard', function () {
//     return Inertia::render('Dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');
// Route::middleware(['auth', 'verified'])->group(function () {
//     Route::get('dashboard', function () {
//        return Inertia::render('Dashboard');
//     })->name('dashboard');
// });
// Route::prefix('admin')->as('admin.')->middleware(['auth', 'verified', 'is_admin'])->group(function () {
//     Route::get('/dashboard', function () {
//         return Inertia::render('Dashboard');
//     })->name('dashboard');
// });

Route::middleware(['auth', 'verified'])->group(function () {
    // Route::get('dashboard', function () {
    //     return Inertia::render('Dashboard');
    // })->name('dashboard');
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('users', Users\Index::class)->name('users.index');
    Route::get('user/create/{user?}', Users\Create::class)->name('user.create');
    Route::get('class/{class_id}/sections', [Users\Create::class, 'sections'])
    ->name('class.sections');
    Route::post('user/store', Users\Store::class)->name('user.store');
    Route::put('user/{user}/toggle', Users\ToggleStatusController::class)->name('user.toggle');
    Route::get('user/{user}/profile', Users\ProfileStatusController::class)->name('user.profile');
    Route::get('user/{user}/payments', Users\UserPaymentController::class)->name('user.payments');
    // Category
    Route::get('categories', Category\Index::class)->name('categories.index');
    Route::post('category/create', Category\CreateController::class)->name('category.create');
    Route::post('category/{category}/update', Category\UpdateController::class)->name('category.update');
    Route::put('category/{category}/toggle', Category\ToggleStatusController::class)->name('category.toggle');
    // Restaurents
    Route::get('restaurents', Restaurent\Index::class)->name('restaurents.index');
    // Route::post('restaurent/create', Restaurent\CreateController::class)->name('restaurent.create');
    // Products Start
    Route::get('products', [ProductController::class, 'index'])->name('products.index');
    // Products End
    // Order Start
    Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('order/create', [OrderController::class, 'create'])->name('order.create');
    Route::post('order/store', [OrderController::class, 'store'])->name('order.store');
    Route::post('order/action/{order}/approved', [OrderController::class, 'approve'])->name('order.action.approved');
    Route::post('order/action/{order}/reject', [OrderController::class, 'reject'])->name('order.action.reject');
    // Order End
    // Fee Generation Start
    // Route::get('fees', [FeeController::class, 'Index'])->name('fees.index');
    Route::get('/fees/structure', [FeeController::class, 'feeStructure'])->name('fees.structure');
    Route::get('fees/structure/create', [FeeController::class, 'feeCreate'])->name('fees.create');
    Route::post('fees/structure/fee-store', [FeeController::class, 'feeStore'])->name('fee.store');
    Route::get('fees/generate', [FeeController::class, 'feeGenerate'])->name('fees.generate');
    Route::put('fees/{fee}/toggle', [FeeController::class, 'feeToggle'])->name('fee.toggle');
    Route::get('fees/{fee}/users', [FeeController::class, 'feeUsers'])->name('fee.users');
    Route::post('fees/{fee}/{user}/custom-amount', [FeeController::class, 'customAmount'])->name('fees.custom.amount');
    Route::get('fees/{fee}/{user}/get-custom-amount', [FeeController::class, 'getCustomAmount'])->name('fees.get.custom.amount');
    // payments
    Route::get('payments', [PaymentController::class, 'Index'])->name('payments.index');
    // Fee Generation End
    // Holiday start
    Route::get('holidays', [HolidayController::class, 'Index'])->name('holidays.index');
    Route::get('holiday/create',  [HolidayController::class, 'Create'])->name('holiday.create');
    Route::post('holiday/store',  [HolidayController::class, 'Store'])->name('holiday.store');
    Route::put('holiday/{holiday}/toggle', [HolidayController::class, 'activeToggle'])->name('holiday.toggle');
    // Route::post('holiday/{holiday}/update', Holiday\UpdateController::class)->name('holiday.update');
    // Route::put('holiday/{holiday}/toggle', Holiday\ToggleStatusController::class)->name('holiday.toggle');
    // Holiday end
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
