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
            // 'guest.password' => ['nullable', 'string', 'min:6'], // No longer needed — guests don't create accounts

            // Items — support both variant_id (variant product) and product_id (simple product)
            'items' => ['required', 'array', 'min:1'],
            'items.*.variant_id' => ['nullable', 'integer', 'exists:variants,id'],
            'items.*.product_id' => ['nullable', 'integer', 'exists:products,id'],
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

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $items = $this->input('items', []);

            foreach ($items as $index => $item) {
                $hasVariant = !empty($item['variant_id']);
                $hasProduct = !empty($item['product_id']);

                if (!$hasVariant && !$hasProduct) {
                    $validator->errors()->add(
                        "items.{$index}",
                        'Each item must have either a variant_id or product_id.'
                    );
                }

                if ($hasVariant && $hasProduct) {
                    $validator->errors()->add(
                        "items.{$index}",
                        'Each item must have only a variant_id or product_id, not both.'
                    );
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'guest.email.email' => 'Please provide a valid email address.',
            'items.required' => 'Please add at least one item to your order.',
            'items.*.variant_id.exists' => 'One or more selected products are no longer available.',
            'items.*.product_id.exists' => 'One or more selected products are no longer available.',
            'shippingAddress.required' => 'Please provide a shipping address.',
            'paymentMethod.id.in' => 'Invalid payment method selected.',
        ];
    }
}
