<?php

namespace App\Modules\Auth\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'mobile' => $this->mobile,
            'role' => $this->role->slug,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'mobile_verified_at' => $this->mobile_verified_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
