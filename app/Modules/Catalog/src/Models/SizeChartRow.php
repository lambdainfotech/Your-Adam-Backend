<?php

declare(strict_types=1);

namespace App\Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SizeChartRow extends Model
{
    use HasFactory;

    protected $fillable = [
        'size_chart_id',
        'size',
        'measurements',
        'sort_order',
    ];

    protected $casts = [
        'measurements' => 'array',
    ];

    public function sizeChart(): BelongsTo
    {
        return $this->belongsTo(SizeChart::class);
    }
}
