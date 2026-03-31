<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SizeChartRow extends Model
{
    use HasFactory;

    protected $table = 'size_chart_rows';

    protected $fillable = [
        'size_chart_id',
        'size_name',
        'measurements',
        'sort_order',
    ];

    protected $casts = [
        'measurements' => 'array',
        'sort_order' => 'integer',
    ];

    public function sizeChart(): BelongsTo
    {
        return $this->belongsTo(SizeChart::class);
    }
}
