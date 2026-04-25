<?php

declare(strict_types=1);

namespace App\Modules\Catalog\Models;

use App\Modules\Catalog\Enums\AttributeType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Attribute extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'type',
        'is_filterable',
        'is_variation',
        'sort_order',
    ];

    protected $casts = [
        'type' => AttributeType::class,
    ];

    public function values(): HasMany
    {
        return $this->hasMany(AttributeValue::class);
    }
}
