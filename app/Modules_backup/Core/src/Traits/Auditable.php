<?php

declare(strict_types=1);

namespace App\Modules\Core\Traits;

trait Auditable
{
    /**
     * Boot the auditable trait.
     */
    public static function bootAuditable(): void
    {
        static::creating(function ($model) {
            if (auth()->check()) {
                $model->created_by = auth()->id();
            }
        });

        static::updating(function ($model) {
            if (auth()->check()) {
                $model->updated_by = auth()->id();
            }
        });
    }

    /**
     * Initialize the auditable trait.
     */
    public function initializeAuditable(): void
    {
        $this->casts['created_by'] = 'integer';
        $this->casts['updated_by'] = 'integer';
    }

    /**
     * Get the user who created this model.
     */
    public function creator()
    {
        return $this->belongsTo(\App\Modules\Auth\src\Models\User::class, 'created_by');
    }

    /**
     * Get the user who last updated this model.
     */
    public function updater()
    {
        return $this->belongsTo(\App\Modules\Auth\src\Models\User::class, 'updated_by');
    }
}
