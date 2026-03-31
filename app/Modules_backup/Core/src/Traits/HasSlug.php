<?php

declare(strict_types=1);

namespace App\Modules\Core\Traits;

use Illuminate\Support\Str;

trait HasSlug
{
    /**
     * Boot the trait.
     */
    public static function bootHasSlug(): void
    {
        static::creating(function ($model) {
            $model->generateSlug();
        });

        static::updating(function ($model) {
            if ($model->isDirty($model->getSlugSourceColumn())) {
                $model->generateSlug();
            }
        });
    }

    /**
     * Generate and set the slug.
     */
    protected function generateSlug(): void
    {
        $source = $this->getSlugSourceColumn();
        $slugField = $this->getSlugColumn();
        
        $slug = Str::slug($this->{$source});
        
        // Ensure unique slug
        $originalSlug = $slug;
        $count = 1;
        
        while ($this->slugExists($slug)) {
            $slug = $originalSlug . '-' . $count;
            $count++;
        }
        
        $this->{$slugField} = $slug;
    }

    /**
     * Check if slug already exists.
     */
    protected function slugExists(string $slug): bool
    {
        $query = static::where($this->getSlugColumn(), $slug);
        
        if ($this->exists) {
            $query->where($this->getKeyName(), '!=', $this->getKey());
        }
        
        return $query->exists();
    }

    /**
     * Get the source column for slug generation.
     */
    protected function getSlugSourceColumn(): string
    {
        return property_exists($this, 'slugSourceColumn') 
            ? $this->slugSourceColumn 
            : 'name';
    }

    /**
     * Get the slug column name.
     */
    protected function getSlugColumn(): string
    {
        return property_exists($this, 'slugColumn') 
            ? $this->slugColumn 
            : 'slug';
    }

    /**
     * Get route key name for route model binding.
     */
    public function getRouteKeyName(): string
    {
        return $this->getSlugColumn();
    }
}
