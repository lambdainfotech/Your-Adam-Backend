<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'bn_name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
