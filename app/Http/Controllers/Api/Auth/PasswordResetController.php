<?php

namespace App\Http\Controllers\Api\Auth;

use App\Customs\Services\PasswordResetService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PasswordResetController extends Controller
{
    protected $passwordResetService;

    public function __construct(PasswordResetService $passwordResetService)
    {
        $this->passwordResetService = $passwordResetService;
    }

    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        return $this->passwordResetService->sendResetLink($request->email);
    }

    // app/Http/Controllers/Api/Auth/PasswordResetController.php

public function resetPassword(Request $request)
{
    $request->validate([
        'token' => 'required',
        'password' => 'required|min:8|confirmed'
    ]);

    try {
        $response = $this->passwordResetService->resetPassword(
            $request->token,
            $request->password
        );

        return response()->json($response);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], 422);
    }
}

    public function validateToken(Request $request)
    {
        $request->validate([
            'token' => 'required'
        ]);

        return $this->passwordResetService->validateToken($request->token);
    }

    public function showResetForm($token)
    {
        return view('auth.passwords.reset', ['token' => $token]);
    }
}