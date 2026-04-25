<?php

declare(strict_types=1);

namespace App\Modules\Sales\Http\Requests;

use App\Modules\Sales\Enums\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class CreateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'address_id' => ['required', 'integer', 'exists:addresses,id'],
            'payment_method' => ['required', 'string', new Enum(PaymentMethod::class)],
            'coupon_code' => ['nullable', 'string', 'min:3', 'max:50'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'address_id.required' => 'Please select a delivery address.',
            'address_id.exists' => 'The selected address does not exist.',
            'payment_method.required' => 'Please select a payment method.',
            'payment_method.enum' => 'Invalid payment method selected.',
            'notes.max' => 'Notes cannot exceed 1000 characters.',
        ];
    }
}
