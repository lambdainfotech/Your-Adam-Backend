<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Events;

use App\Modules\Product\Models\Variant;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LowStockAlert
{
    use Dispatchable, SerializesModels;

    public function __construct(public Variant $variant)
    {
    }
}
