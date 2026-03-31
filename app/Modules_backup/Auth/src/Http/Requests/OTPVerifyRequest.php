<?php

namespace App\Modules\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OTPVerifyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'mobile' => ['required', 'string'],
            'otp' => ['required', 'string', 'size:6'],
            'reference' => ['required', 'string'],
            'is_registration' => ['required', 'boolean'],
            'password' => ['required_if:is_registration,true', 'string', 'min:6', 'confirmed'],
            'full_name' => ['required_if:is_registration,true', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'mobile.required' => 'Mobile number is required.',
            'otp.required' => 'OTP is required.',
            'otp.size' => 'OTP must be exactly 6 digits.',
            'reference.required' => 'Reference is required.',
            'is_registration.required' => 'Registration flag is required.',
            'is_registration.boolean' => 'Registration flag must be true or false.',
            'password.required_if' => 'Password is required for registration.',
            'password.min' => 'Password must be at least 6 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
            'full_name.required_if' => 'Full name is required for registration.',
            'full_name.max' => 'Full name cannot exceed 255 characters.',
            'email.email' => 'Please enter a valid email address.',
            'email.max' => 'Email cannot exceed 255 characters.',
        ];
    }
}
