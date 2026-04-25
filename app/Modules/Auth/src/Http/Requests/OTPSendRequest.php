<?php

namespace App\Modules\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OTPSendRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'mobile' => ['required', 'string', 'regex:/^\+8801[3-9]\d{8}$/'],
            'purpose' => ['required', 'string', 'in:registration,login,password_reset'],
        ];
    }

    public function messages(): array
    {
        return [
            'mobile.required' => 'Mobile number is required.',
            'mobile.regex' => 'Please enter a valid Bangladeshi mobile number (e.g., +8801XXXXXXXXX).',
            'purpose.required' => 'Purpose is required.',
            'purpose.in' => 'Purpose must be one of: registration, login, password_reset.',
        ];
    }
}
