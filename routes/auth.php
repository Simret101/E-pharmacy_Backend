<?php
use App\Http\Controllers\ChatController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\DrugConroller;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\Profile\PasswordController;
use App\Http\Controllers\Api\DrugLikeController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\PatientController; 
use App\Http\Controllers\Api\PharmacistController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\Api\PrescriptionController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\Api\ForgotPasswordController;
use App\Http\Controllers\Api\ResetPasswordController;
use App\Http\Controllers\Api\InventoryLogController;
use App\Http\Controllers\Api\PlaceController;
use App\Http\Controllers\Api\NotificationController;


Route::get('/verify-email/{token}', [AdminController::class, 'verifyEmail']);
Route::match(['get', 'post'], '/admin/pharmacists/{id}/status', [AdminController::class, 'updatePharmacistStatus']);


// Authentication Routes
Route::post('auth/register', [AuthController::class, 'register']);
Route::post('auth/login', [AuthController::class, 'login']);
Route::post('auth/refresh_token', [AuthController::class, 'refreshToken']);
Route::post('auth/verify_user_email', [AuthController::class, 'verifyUserEmail']);
Route::post('auth/resend_email_verification_link', [AuthController::class, 'resendEmailVerificationLink']);
Route::post('/forgot-password', [PasswordController::class, 'sendResetLink']);
Route::post('/resend_email_verification_link', [AuthController::class, 'resendEmailVerificationLink']);
Route::post('/forgot-password', [PasswordController::class, 'sendResetLink']);
Route::get('/password/reset/{token}', [PasswordController::class, 'showResetForm'])->name('password.reset');

//Google oauth Routes

Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback']);
Route::get('/auth/google/url', [GoogleAuthController::class, 'redirect']);


//Places Route


Route::get('/user-locations', [PlaceController::class, 'userLocations']);
Route::get('/app', function () {
    return view('app');
});



// Protected routes
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // User Profile
    Route::put('/profile/update', [UserController::class, 'updateProfile']);
    Route::post('/send-verification-email', [EmailVerificationController::class, 'sendVerificationEmail']);

    // Message Routes
    Route::post('/messages/send', [MessageController::class, 'sendMessage']);
    Route::get('/messages/conversation/{userId}', [MessageController::class, 'getConversationWithUser']);
    Route::patch('/messages/{id}/read', [MessageController::class, 'markAsRead']);
    Route::delete('/messages/{id}', [MessageController::class, 'deleteMessage']);

    // Payment Routes
    Route::post('/payments/process', [PaymentController::class, 'processPayment']);

    // Notification Routes
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);

    // Image Routes
    Route::post('/image', [ImageController::class, 'store']);
    Route::get('/image', [ImageController::class, 'show']);
    Route::delete('/image', [ImageController::class, 'destroy']);

    // Order Routes
    Route::get('/orders', [OrderController::class, 'userOrders']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    Route::post('/orders', [OrderController::class, 'store']);

    // Prescription Routes
    Route::post('/prescriptions/upload', [PrescriptionController::class, 'upload']);

    // Drug Like Routes
    Route::post('/drugs/togglelike', [DrugLikeController::class, 'togglelike']);

    // Admin routes
    Route::middleware(['admin'])->group(function () {
        Route::get('/admin/pharmacists', [PharmacistController::class, 'index']);
        Route::get('/admin/pharmacists/all', [AdminController::class, 'getAllPharmacists']);
        Route::get('/admin/patients', [PatientController::class, 'getAllPatients']);
        Route::get('/admin/patients/{id}', [PatientController::class, 'show']);
        Route::put('/admin/patients/{id}', [PatientController::class, 'update']);
        Route::delete('/admin/patients/{id}', [PatientController::class, 'destroy']);

        Route::get('/pharmacists/{id}/license-image', [AdminController::class, 'viewLicenseImage']);
        Route::put('/pharmacists/{id}/approve', [AdminController::class, 'approvePharmacist']);
        Route::put('/pharmacists/{id}/reject', [AdminController::class, 'rejectPharmacist']);
        Route::get('/admin/pharmacists/pending', [AdminController::class, 'getPendingPharmacists']);

        Route::post('/admin/pharmacists', [PharmacistController::class, 'store']);
        Route::put('/admin/pharmacists/{pharmacist}', [PharmacistController::class, 'update']);
        Route::delete('/admin/pharmacists/{pharmacist}', [PharmacistController::class, 'destroy']);

        Route::get('/admin/orders', [OrderController::class, 'adminOrders']);
    });

    // Pharmacist Routes
    Route::middleware(['pharmacist'])->group(function () {
        // Drug Management
        Route::get('/drugs/my', [DrugConroller::class, 'getMyDrugs']);
        Route::post('/drugs', [DrugConroller::class, 'store']);
        Route::put('/drugs/{id}', [DrugConroller::class, 'update']);
        Route::delete('/drugs/{id}', [DrugConroller::class, 'destroy']);
        
        // Inventory Management
        Route::get('/inventory/logs', [InventoryLogController::class, 'index']);
        Route::get('/low-stock/alerts', [DrugConroller::class, 'lowStockAlerts']);
        Route::patch('/drugs/{drug}/adjust-stock', [DrugConroller::class, 'adjustStock']);

        // Prescriptions
        Route::post('/prescriptions/dispense/{uid}', [PrescriptionController::class, 'dispense']);
    });

    Route::get('/my-drugs', [DrugConroller::class, 'getMyDrugs']);
});


// Public Drug Routes
Route::get('drugs', [DrugConroller::class, 'index']); 
Route::get('drugs/{id}', [DrugConroller::class, 'show']); 




