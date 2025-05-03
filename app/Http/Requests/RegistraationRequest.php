<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegistraationRequest extends FormRequest
{
   
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'email' => ['required', 'email:filter', Rule::unique('users', 'email')],
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/[A-Z]/',     
                'regex:/[a-z]/',      
                'regex:/[0-9]/',      
                'regex:/[@$!%*?&]/', 
                'confirmed',
            ],
            'is_role' => ['required', 'integer', Rule::in([0, 1, 2])],
            'pharmacy_name' => ['required_if:is_role,2', 'string', 'max:255'],
            'address' => ['required_if:is_role,2', 'string', 'max:255'],
            'lat' => ['required_if:is_role,2', 'numeric', 'between:-90,90'],
            'lng' => ['required_if:is_role,2', 'numeric', 'between:-180,180'],
            'phone' => ['required', 'string', 'regex:/^[0-9]{10,15}$/'],
            'license_image' => ['required_if:is_role,2', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'tin_image' => ['required_if:is_role,2', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'tin_number' => ['required_if:is_role,2', 'string', 'max:255'],
            'account_number' => ['required_if:is_role,2', 'string', 'max:255'],
            'bank_name' => ['required_if:is_role,2', 'string', 'max:255'],
        ];
    }

    
    public function messages(): array
    {
        return [
            'name.required' => 'The name field is required.',
            'pharmacy_name.required_if' => 'The pharmacy name is required for pharmacists.',
            'name.min' => 'The name must be at least 2 characters.',
            'email.required' => 'The email field is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email is already registered.',
            'password.required' => 'The password field is required.',
            'password.min' => 'The password must be at least 8 characters long.',
            'password.confirmed' => 'The password confirmation does not match.',
            'is_role.in' => 'Invalid role selection.',
            'is_role.required' => 'The role field is required.',
            'address.required_if' => 'The address is required for pharmacists.',
            'lat.required_if' => 'The latitude is required for pharmacists.',
            'lng.required_if' => 'The longitude is required for pharmacists.',
            'phone.required' => 'The phone number is required.',
            'phone.regex' => 'The phone number must be between 10 and 15 digits.',
            'license_image.required_if' => 'The license image is required for pharmacists.',
            'license_image.image' => 'The license image must be an image file.',
            'license_image.mimes' => 'The license image must be a JPEG, PNG, or JPG file.',
            'license_image.max' => 'The license image should not exceed 2MB.',
            'tin_image.required_if' => 'The TIN document is required for pharmacists.',
            'tin_image.image' => 'The TIN document must be an image file.',
            'tin_image.mimes' => 'The TIN document must be a JPEG, PNG, or JPG file.',
            'tin_image.max' => 'The TIN document should not exceed 2MB.',
            'tin_number.required_if' => 'The TIN number is required for pharmacists.',
            'account_number.required_if' => 'The account number is required for pharmacists.',
            'bank_name.required_if' => 'The bank name is required for pharmacists.',
        ];
    }
}
