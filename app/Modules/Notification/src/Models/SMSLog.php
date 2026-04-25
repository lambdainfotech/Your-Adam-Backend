<?php

declare(strict_types=1);

namespace App\Modules\Notification\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SMSLog extends Model
{
    protected $fillable = [
        'user_id',
        'mobile',
        'template',
        'message',
        'provider',
        'status',
        'provider_response',
        'error_message',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
