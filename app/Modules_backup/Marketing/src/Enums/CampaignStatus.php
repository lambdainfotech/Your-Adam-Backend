<?php

declare(strict_types=1);

namespace App\Modules\Marketing\Enums;

enum CampaignStatus: string
{
    case ACTIVE = 'active';
    case SCHEDULED = 'scheduled';
    case EXPIRED = 'expired';
    case DISABLED = 'disabled';
}
