<?php

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\AdminController;

use App\Http\Controllers\Api\Auth\PasswordResetController;
use App\Http\Controllers\Api\Auth\AuthController;

// In your routes/api.php
Route::get('/auth/token', function (Request $request) {
    return response()->json([
        'token' => $request->session()->get('access_token'),
        'user' => $request->session()->get('user')
    ]);
});


Route::post('/password/reset/otp', [PasswordResetController::class, 'sendResetLink']);
Route::post('/password/reset', [PasswordResetController::class, 'resetPassword']);
Route::post('/password/otp/validate', [PasswordResetController::class, 'validateToken']);
// Route::get('/password/reset/{token}', [PasswordResetController::class, 'showResetForm']);
Route::get('/health-check', function () {
    return response()->json(['status' => 'ok']);
});

// Google OAuth Routes
// In routes/api.php

// Google Authentication Routes


// Logout Route
Route::post('/auth/logout', [AuthController::class, 'logout'])->middleware('auth:api');

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Chatbot routes
Route::post('/chatbot/drug-info', [\App\Http\Controllers\Api\ChatbotController::class, 'getDrugInfo']);
Route::get('/chatbot/history', [\App\Http\Controllers\Api\ChatbotController::class, 'getUserChatHistory']);
Route::get('/chatbot/history/drug/{drug_name}', [\App\Http\Controllers\Api\ChatbotController::class, 'getDrugChatHistory']);
Route::get('/chatbot/health', [\App\Http\Controllers\Api\ChatbotController::class, 'checkHealth']);

// Cart Routes
// Route::apiResource('carts', \App\Http\Controllers\Api\CartController::class);
// Route::post('carts/checkout', [\App\Http\Controllers\Api\CartController::class, 'checkout']);

require __DIR__ . '/auth.php';

Route::middleware('auth')->group(function () {
    Route::post('/admin/approve/{id}', [AdminController::class, 'approvePharmacist']);
    Route::get('/admin/license-image/{id}', [AdminController::class, 'viewLicenseImage']);

   
});
Route::patch('/prescriptions/{id}/approve', [OrderController::class, 'approvePrescription']);
Route::patch('/prescriptions/{id}/reject', [OrderController::class, 'rejectPrescription']);
Route::patch('/prescriptions/{id}/refill', [OrderController::class, 'updateRefill']);
// Public routes
//Route::get('/pharmacists', [AdminController::class, 'getAllPharmacists']);







