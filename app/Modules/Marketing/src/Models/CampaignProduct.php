<?php

declare(strict_types=1);

namespace App\Modules\Marketing\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampaignProduct extends Model
{
    use HasFactory;

    protected $table = 'campaign_products';

    public $timestamps = false;

    protected $fillable = [
        'campaign_id',
        'product_id',
        'variant_id',
    ];

    protected $casts = [
        'campaign_id' => 'integer',
        'product_id' => 'integer',
        'variant_id' => 'integer',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(Variant::class);
    }
}
