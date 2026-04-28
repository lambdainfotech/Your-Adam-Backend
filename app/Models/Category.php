<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'description',
        'image',
        'hero_image',
        'cover_image',
        'banner',
        'sort_order',
        'is_active',
        'meta_title',
        'meta_description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'category_id');
    }

    public function subCategoryProducts(): HasMany
    {
        return $this->hasMany(Product::class, 'sub_category_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    public function sizeCharts(): HasMany
    {
        return $this->hasMany(SizeChart::class);
    }

    public function allProducts(): HasMany
    {
        return $this->hasMany(Product::class)
            ->where(function ($q) {
                $q->whereColumn('categories.id', 'products.category_id')
                  ->orWhereIn('products.category_id', function ($query) {
                      $query->select('id')
                          ->from('categories')
                          ->where('parent_id', $this->id);
                  });
            });
    }

    /**
     * Override products_count to use sub_category_id for subcategories
     */
    public function getProductsCountAttribute($value): int
    {
        if ($this->parent_id) {
            return (int) ($this->attributes['sub_category_products_count'] ?? 0);
        }
        return (int) $value;
    }

    public function getBreadcrumbAttribute(): array
    {
        $breadcrumb = [];
        $category = $this;
        
        while ($category) {
            $breadcrumb[] = $category;
            $category = $category->parent;
        }
        
        return array_reverse($breadcrumb);
    }
}
