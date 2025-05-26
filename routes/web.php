<?php

use Illuminate\Support\Facades\Route;
use App\Events\PersonMoved;

use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Api\AdminController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\PatientController;
use App\Customs\Services\EmailVerificationService;
use App\Http\Controllers\Api\Auth\PasswordResetController;
use App\Http\Controllers\GoogleAuthController;


// In routes/web.php
// Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('google.redirect');
// Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('google.callback');
Route::get('/', function () {
    return view('welcome');
});
Route::get('/app',function(){
    return view('app');
});

Route::get('/move',function(){
    event(new PersonMoved(40.7128, -74.0060));
    
});
Route::get('/verify-email/{token}', function ($token) {
    $email = request()->query('email');
    return app(EmailVerificationService::class)->verifyEmail($email, $token);
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

// Route::get('/email/verified', function () {
//     return view('auth.verified');
// })->name('verification.success');
// Route::get('/password/reset/', function () {
//     return view('emails.password-reset');
// })->name('password.request');



Route::get('/email-verified', function() {
    return view('auth.email-verified-success');
})->name('email-verified');


// Home Route
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Route::post('/password/reset-link', [PasswordResetController::class, 'sendResetLink'])->name('api.password.reset.link');
// Route::get('/password/reset/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset.form');
// Route::post('/password/reset', [PasswordResetController::class, 'reset'])->name('password.reset');
// Route::post('/password/email', [PasswordResetController::class, 'sendResetLink'])->name('password.email');


// In routes/web.php
Route::get('/password/reset/{token}', function ($token) {
    return view('auth.passwords.reset', ['token' => $token]);
});
// Password Reset Routes
Route::post('/password/email', [PasswordResetController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/password/reset/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
Route::post('/password/reset', [PasswordResetController::class, 'reset'])->name('password.update');
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
Route::get('/admin/pharmacists/{id}/action', [AdminController::class, 'handleEmailAction'])
    ->name('admin.pharmacist.action')
    ->middleware(['auth', 'admin']);

// Admin Dashboard Route
Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])
    ->name('admin.dashboard')
    ->middleware(['auth', 'admin']);


// Route::post('/chapa/callback', [PaymentController::class, 'chapaCallback'])->name('chapa.callback');
// Route::get('/chapa/success/{order_id}', [PaymentController::class, 'chapaSuccess'])->name('chapa.success');
// Route::get('/chapa/init/{orderId}', [PaymentController::class, 'showPaymentForm'])->name('chapa.init');
// Route::post('/chapa/init/{orderId}', [PaymentController::class, 'chapaPay'])->name('chapa.pay');

Route::get('/chapa/pay/{orderId}', [PaymentController::class, 'showPaymentForm'])->name('chapa.init');
Route::post('/chapa/pay', [PaymentController::class, 'chapaPay'])->name('chapa.pay');
Route::get('/chapa/success/{order_id}', [PaymentController::class, 'chapaSuccess'])->name('chapa.success');