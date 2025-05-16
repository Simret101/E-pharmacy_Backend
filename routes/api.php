<?php

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\Api\Auth\AuthController;

Route::get('/health-check', function () {
    return response()->json(['status' => 'ok']);
});

// Google OAuth Routes
Route::get('/auth/google', [\App\Http\Controllers\Api\Auth\GoogleAuthController::class, 'redirect']);
Route::get('/auth/google/callback', [\App\Http\Controllers\Api\Auth\GoogleAuthController::class, 'callback']);

// Logout Route
Route::post('/auth/logout', [AuthController::class, 'logout'])->middleware('auth:api');

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Cart Routes
// Route::apiResource('carts', \App\Http\Controllers\Api\CartController::class);
// Route::post('carts/checkout', [\App\Http\Controllers\Api\CartController::class, 'checkout']);

require __DIR__ . '/auth.php';

Route::middleware('auth')->group(function () {
    Route::post('/admin/approve/{id}', [AdminController::class, 'approvePharmacist']);
    Route::get('/admin/license-image/{id}', [AdminController::class, 'viewLicenseImage']);

   
});

// Public routes
//Route::get('/pharmacists', [AdminController::class, 'getAllPharmacists']);







