<?php

declare(strict_types=1);

namespace App\Modules\Courier\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Courier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'logo',
        'phone',
        'email',
        'website',
        'tracking_url_template',
        'api_config',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'api_config' => 'array',
        'is_active' => 'boolean',
    ];

    public function assignments(): HasMany
    {
        return $this->hasMany(CourierAssignment::class);
    }
}
