<?php

declare(strict_types=1);

namespace App\Modules\User\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => 'nullable|in:home,office,other',
            'full_name' => 'required|string|max:255',
            'mobile' => 'required|string|max:20',
            'address' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'required|string|max:100',
            'district' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'landmark' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:100',
            'is_default' => 'boolean',
        ];
    }
}
