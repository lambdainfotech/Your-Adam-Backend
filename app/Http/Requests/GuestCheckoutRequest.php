<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GuestCheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Guest info
            'guest.name' => ['required', 'string', 'max:255'],
            'guest.email' => ['required', 'email'],
            'guest.phone' => ['required', 'string', 'max:20'],
            'guest.password' => ['required', 'string', 'min:6'],

            // Items
            'items' => ['required', 'array', 'min:1'],
            'items.*.variant_id' => ['required', 'integer', 'exists:variants,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],

            // Shipping Address
            'shippingAddress' => ['required', 'array'],
            'shippingAddress.name' => ['required', 'string', 'max:255'],
            'shippingAddress.phone' => ['required', 'string', 'max:20'],
            'shippingAddress.address' => ['required', 'string', 'max:255'],
            'shippingAddress.city' => ['required', 'string', 'max:100'],
            'shippingAddress.district' => ['nullable', 'string', 'max:100'],
            'shippingAddress.postcode' => ['required', 'string', 'max:20'],

            // Payment
            'paymentMethod.id' => ['required', 'string', 'in:cod,aamarpay'],
            'paymentMethod.name' => ['nullable', 'string', 'max:100'],

            // Order Summary (for reference / shipping cost)
            'orderSummary' => ['nullable', 'array'],
            'orderSummary.subtotal' => ['nullable', 'numeric', 'min:0'],
            'orderSummary.shippingCost' => ['nullable', 'numeric', 'min:0'],
            'orderSummary.discount' => ['nullable', 'numeric', 'min:0'],
            'orderSummary.total' => ['nullable', 'numeric', 'min:0'],
            'orderSummary.freeShippingThreshold' => ['nullable', 'numeric', 'min:0'],

            // Note
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'guest.email.unique' => 'This email is already registered. Please login or use a different email.',
            'items.required' => 'Please add at least one item to your order.',
            'items.*.variant_id.exists' => 'One or more selected products are no longer available.',
            'shippingAddress.required' => 'Please provide a shipping address.',
            'paymentMethod.id.in' => 'Invalid payment method selected.',
        ];
    }
}
