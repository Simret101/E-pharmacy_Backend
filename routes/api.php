<?php

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AdminController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\Api\Auth\AuthController;

// Google OAuth Routes
Route::get('/auth/google/url', [GoogleAuthController::class, 'redirect']);
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback']);

// Logout Route
Route::post('/auth/logout', [AuthController::class, 'logout'])->middleware('auth:api');

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

require __DIR__ . '/auth.php';

// // Admin Management Routes
// Route::middleware('auth:api')->group(function () {
//     Route::post('/admin/create', [AdminController::class, 'createAdmin']);
//     Route::get('/admin/list', [AdminController::class, 'listAdmins']);
//     Route::put('/admin/{id}/status', [AdminController::class, 'updateAdminStatus']);
    
//     // New Pharmacist Management Routes
//     Route::post('/admin/pharmacist/{id}/approve', [AdminController::class, 'approveNewPharmacist']);
//     Route::post('/admin/pharmacist/{id}/reject', [AdminController::class, 'rejectNewPharmacist']);
// });







