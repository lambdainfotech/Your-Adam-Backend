<?php

namespace App\Modules\Sales\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CustomerOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $hasAddressId = $this->filled('address_id');

        return [
            // Items — support both variant_id (variant product) and product_id (simple product)
            'items' => ['required', 'array', 'min:1'],
            'items.*.variant_id' => ['nullable', 'integer', 'exists:variants,id'],
            'items.*.product_id' => ['nullable', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],

            // Address: either use saved address ID or provide inline shipping address
            'address_id' => ['nullable', 'integer', 'exists:addresses,id'],

            // Shipping Address (required only when address_id is not provided)
            'shippingAddress' => [$hasAddressId ? 'nullable' : 'required', 'array'],
            'shippingAddress.name' => [$hasAddressId ? 'nullable' : 'required', 'string', 'max:255'],
            'shippingAddress.phone' => [$hasAddressId ? 'nullable' : 'required', 'string', 'max:20'],
            'shippingAddress.address' => [$hasAddressId ? 'nullable' : 'required', 'string', 'max:255'],
            'shippingAddress.city' => [$hasAddressId ? 'nullable' : 'required', 'string', 'max:100'],
            'shippingAddress.district' => ['nullable', 'string', 'max:100'],
            'shippingAddress.postcode' => [$hasAddressId ? 'nullable' : 'required', 'string', 'max:20'],

            // Payment
            'paymentMethod' => ['required', 'array'],
            'paymentMethod.id' => ['required', 'string', 'in:cod,aamarpay'],
            'paymentMethod.name' => ['nullable', 'string', 'max:100'],

            // Order Summary (for reference)
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

            // Ensure exactly one of address_id or shippingAddress is provided
            $hasAddressId = $this->filled('address_id');
            $hasShippingAddress = $this->has('shippingAddress') && is_array($this->input('shippingAddress'));

            if (!$hasAddressId && !$hasShippingAddress) {
                $validator->errors()->add('address', 'Please provide either an address_id or a shippingAddress.');
            }

            if ($hasAddressId && $hasShippingAddress) {
                $validator->errors()->add('address', 'Please provide either an address_id or a shippingAddress, not both.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'items.required' => 'Please add at least one item to your order.',
            'items.*.variant_id.exists' => 'One or more selected products are no longer available.',
            'items.*.product_id.exists' => 'One or more selected products are no longer available.',
            'address_id.exists' => 'The selected address does not exist.',
            'paymentMethod.id.in' => 'Invalid payment method selected.',
        ];
    }
}
