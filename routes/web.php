<?php

use Illuminate\Support\Facades\Route;
use App\Events\PersonMoved;

use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Api\AdminController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\PatientController;




Route::get('/', function () {
    return view('welcome');
});
Route::get('/app',function(){
    return view('app');
});

Route::get('/move',function(){
    event(new PersonMoved(40.7128, -74.0060));
    
});


Route::get('/email/verify', function () {
    return view('email_verification');
})->name('verification.notice');

Route::get('/email/verification-failed', function () {
    return view('auth.verification-failed');
})->name('verification.failed');
// Route::get('/forgot-password', function () {
//     return view('forgot_password');
// })->name('password.request');

Route::get('/email/verified', function () {
    return view('auth.verified');
})->name('verification.success');
Route::get('/password/reset/', function () {
    return view('emails.password_reset');
})->name('password.request');





// Home Route
Route::get('/', function () {
    return view('welcome');
})->name('home');

// PayPal Payment Route
Route::post('/paypal/process', [PaymentController::class, 'pay'])->name('paypal.process');

// Google Authentication Routes
Route::get('/auth/google', [\App\Http\Controllers\GoogleAuthController::class, 'redirect'])->name('google.redirect');
Route::get('/auth/google/callback', [\App\Http\Controllers\GoogleAuthController::class, 'callback'])->name('google.callback');
Route::get('/admin/license-image/{id}', [AdminController::class, 'viewLicenseImage']);
Route::get('/admin/pharmacist/action/{id}', [AdminController::class, 'handlePharmacistAction'])->name('admin.pharmacist.action');
Route::get('/success', [PaymentController::class, 'success'])->name('payment.success');
Route::get('/error', [PaymentController::class, 'error'])->name('payment.error');


Route::get('/pay/{orderId}', function ($orderId) {
    $order = DB::table('orders')->where('id', $orderId)->first();

    if (!$order) {
        abort(404, 'Order not found');
    }

    return view('paypal', [
        'order_id' => $order->id,
        'amount' => $order->total_amount
    ]);
})->name('payment');

// Admin Pharmacist Action Routes
Route::get('/admin/pharmacists/{id}/action', [App\Http\Controllers\Api\AdminController::class, 'handlePharmacistAction'])
    ->name('admin.pharmacist.action')
    ->middleware(['auth', 'admin']);

// Admin Dashboard Route
Route::get('/admin/dashboard', [App\Http\Controllers\Api\AdminController::class, 'dashboard'])
    ->name('admin.dashboard')
    ->middleware(['auth', 'admin']);
