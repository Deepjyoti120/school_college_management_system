<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Users;
use App\Http\Controllers\Category;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FeeController;
use App\Http\Controllers\OrderController;
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
    Route::get('user/create', Users\Create::class)->name('user.create');
    Route::get('class/{class_id}/sections', [Users\Create::class, 'sections'])
    ->name('class.sections');
    Route::post('user/store', Users\Store::class)->name('user.store');
    Route::put('user/{user}/toggle', Users\ToggleStatusController::class)->name('user.toggle');
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
    Route::get('fees', [FeeController::class, 'index'])->name('fees.index');
    Route::get('fees/structure', [FeeController::class, 'feeStructure'])->name('fees.structure');
    Route::get('fees/generate', [FeeController::class, 'feeGenerate'])->name('fees.generate');
    // Fee Generation End
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
