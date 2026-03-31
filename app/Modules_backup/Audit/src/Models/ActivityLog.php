<?php

declare(strict_types=1);

namespace App\Modules\Audit\Models;

use App\Modules\Auth\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'user_type',
        'action',
        'entity_type',
        'entity_id',
        'old_values',
        'new_values',
        'description',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForEntity($query, string $type, ?int $id = null)
    {
        $query->where('entity_type', $type);
        if ($id) {
            $query->where('entity_id', $id);
        }
        return $query;
    }

    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }
}
