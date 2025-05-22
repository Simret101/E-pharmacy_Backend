<?php
// app/Http/Requests/VerifyOtpRequest.php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerifyOtpRequest extends FormRequest
{
    public function rules()
    {
        return [
            'email' => 'required|email|exists:users',
            'otp' => 'required|digits:6',
            'password' => 'required|min:8|confirmed',
        ];
    }
}