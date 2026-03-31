<?php

declare(strict_types=1);

namespace App\Modules\Sales\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApplyCouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'coupon_code' => ['required', 'string', 'min:3', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'coupon_code.required' => 'Please enter a coupon code.',
            'coupon_code.min' => 'Coupon code is too short.',
            'coupon_code.max' => 'Coupon code is too long.',
        ];
    }
}
