<?php
use App\Http\Controllers\ChatController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\ChatbotController;
use App\Http\Controllers\Api\DrugController;
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

use App\Http\Controllers\Api\Auth\PasswordResetController;

Route::get('/auth/google/redirect', [GoogleAuthController::class, 'redirect']);
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback']);

// routes/api.php
Route::post('/password/request-token', [PasswordController::class, 'requestOtp']);
Route::post('/password/verify-token', [PasswordController::class, 'verifyOtp']);
//Route::post('/password/reset', [PasswordController::class, 'resetPassword']);

// routes/auth.php
// Route::post('/password/email', [PasswordController::class, 'sendResetLink']);
// Route::post('/password/reset', [PasswordController::class, 'resetPassword'])->name('password.update');
// Route::get('/password/reset/{token}', [PasswordController::class, 'showResetForm'])->name('password.reset');

// Route::post('/pay', [PaymentController::class, 'pay'])->name('payment.pay');
// Route::get('/success', [PaymentController::class, 'success'])->name('payment.success');
// Route::get('/error', [PaymentController::class, 'error'])->name('payment.error');

Route::get('/verify-email/{token}', [AdminController::class, 'verifyEmail']);
Route::match(['get', 'post'], '/admin/pharmacists/{id}/status', [AdminController::class, 'updatePharmacistStatus']);
Route::get('/patients/{id}', [PatientController::class, 'getPatientById']);


// Authentication Routes
Route::post('auth/register', [AuthController::class, 'register']);
Route::post('auth/login', [AuthController::class, 'login']);
Route::post('auth/refresh_token', [AuthController::class, 'refreshToken']);
Route::post('auth/verify_user_email', [AuthController::class, 'verifyUserEmail']);
Route::post('auth/resend_email_verification_link', [AuthController::class, 'resendEmailVerificationLink']);
Route::post('/forgot-password', [PasswordController::class, 'sendResetLink']);
Route::post('/resend_email_verification_link', [AuthController::class, 'resendEmailVerificationLink']);
Route::get('/password/reset/{token}', [PasswordController::class, 'showResetForm'])->name('password.reset');
// Route::get('/auth/password/reset/{token}', [PasswordController::class, 'showResetForm'])->name('password.reset');
// Google Authentication Routes
Route::get('/auth/google/{role}/redirect', [GoogleAuthController::class, 'redirect']);
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback']);


// Places Route
Route::get('/user-locations', [PlaceController::class, 'userLocations']);
Route::get('/app', function () {
    return view('app');
});




// Protected routes
Route::middleware(['auth'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::get('/verify-token', [AuthController::class, 'verifyAccessToken']);
    Route::put('/password/change', [PasswordController::class, 'changePassword']);
    // Chatbot routes
    Route::post('/chatbot/drug-info', [ChatbotController::class, 'getDrugInfo']);
    Route::get('/chatbot/history', [ChatbotController::class, 'getUserChatHistory']);
    Route::get('/chatbot/history/drug/{drug_name}', [ChatbotController::class, 'getDrugChatHistory']);
    Route::get('/chatbot/health', [ChatbotController::class, 'checkHealth']);
    
    // Order routes
    Route::post('/orders/{id}/approve', [OrderController::class, 'approveOrder']);
    Route::post('/orders/{id}/reject', [OrderController::class, 'rejectOrder']);
    Route::post('/orders/{id}/process-payment', [OrderController::class, 'processPayment']);
    
    // User Profile
    Route::put('/profile/update', [AuthController::class, 'updateProfile']);
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::resource('carts', CartController::class);
    Route::post('carts/checkout', [CartController::class, 'checkout']);
    // Message Routes
    Route::post('/messages/send', [MessageController::class, 'sendMessage']);
    Route::get('/messages/conversation/{userId}', [MessageController::class, 'getConversationWithUser']);
    Route::patch('/messages/{id}/read', [MessageController::class, 'markAsRead']);
    Route::delete('/messages/{id}', [MessageController::class, 'deleteMessage']);
    Route::get('/messages', [MessageController::class, 'getAllChat']);
    // Payment Routes
    Route::post('/payments/process', [PaymentController::class, 'processPayment']);

    // Notification Routes
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);

    // Image Routes
    
    Route::post('/image', [ImageController::class, 'store']);
    Route::get('/image', [ImageController::class, 'show']);
    Route::put('/image', [ImageController::class, 'update']);
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
        Route::post('/admin/create', [AdminController::class, 'createAdmin']);
        Route::get('/admin/list', [AdminController::class, 'listAdmins']);
        Route::patch('/approve/{id}', [PharmacistController::class, 'approve']);
        Route::patch('/reject/{id}', [PharmacistController::class, 'reject']);
        Route::get('/admin/pharmacists', [PharmacistController::class, 'index']);
 
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

        Route::post('/admin/create', [AdminController::class, 'createAdmin']);
        
        Route::get('/admin/list', [AdminController::class, 'listAdmins']);
        Route::put('/admin/{id}/status', [AdminController::class, 'updateAdminStatus']);

        // New Pharmacist Management Routes
        Route::post('/admin/pharmacist/{id}/approve', [AdminController::class, 'approveNewPharmacist']);
        Route::post('/admin/pharmacist/{id}/reject', [AdminController::class, 'rejectNewPharmacist']);
    });

    // Pharmacist Routes
    Route::middleware(['pharmacist'])->group(function () {
        Route::patch('/orders/{id}/update-prescription-status', [OrderController::class, 'updatePrescriptionStatus']);
        // Drug Management
        Route::get('/drugs/my', [DrugController::class, 'getMyDrugs']);
        Route::post('/drugs', [DrugController::class, 'store']);
        Route::put('/drugs/{id}', [DrugController::class, 'update']);
        Route::delete('/drugs/{id}', [DrugController::class, 'destroy']);
        
        // Inventory Management
        Route::get('/inventory/logs', [InventoryLogController::class, 'index']);
        Route::get('/low-stock/alerts', [DrugController::class, 'lowStockAlerts']);
        Route::patch('/drugs/{drug}/adjust-stock', [DrugController::class, 'adjustStock']);
        Route::get('/pharmacist/orders/{id}', [OrderController::class, 'getPharmacistOrderById']);
        // Prescriptions
        Route::get('/pharmacist/orders', [OrderController::class, 'getPharmacistOrders']);
        Route::post('/prescriptions/dispense/{uid}', [PrescriptionController::class, 'dispense']);
        
    });

  
 
   
});


Route::post('/password/reset-link', [PasswordResetController::class, 'sendResetLink'])->name('api.password.reset.link');
// Route::post('/password/reset', [PasswordResetController::class, 'reset'])->name('api.password.reset');
// Route::post('/password/validate-token', [PasswordResetController::class, 'validateToken'])->name('api.password.validate.token');
// Public Drug Routes
Route::get('drugs', [DrugController::class, 'index']);
Route::get('drugs/category/{category}', [DrugController::class, 'getByCategory']);
Route::get('drugs/{id}', [DrugController::class, 'show']);
Route::get('drugs/created-by/{username}', [DrugController::class, 'getDrugsByCreator']);
Route::get('/admin/pharmacists/all', [AdminController::class, 'getAllPharmacists']);
Route::get('/pharmacists/{id}', [PharmacistController::class, 'show']);
Route::get('/pharmacists/username/{username}', [PharmacistController::class, 'getByUsername']);
Route::get('/patients/{id}', [PharmacistController::class, 'getPatient']);



