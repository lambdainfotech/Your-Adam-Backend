<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturnRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'name',
        'email',
        'phone',
        'items',
        'reason',
        'details',
        'status',
        'admin_notes',
        'ip_address',
        'user_agent',
        'processed_at',
    ];

    protected $casts = [
        'processed_at' => 'datetime',
    ];
}
