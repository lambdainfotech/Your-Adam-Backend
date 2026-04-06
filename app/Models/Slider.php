<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Slider extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'subtitle',
        'title_color',
        'subtitle_color',
        'banner_image',
        'button_text',
        'button_url',
        'button_text_color',
        'button_bg_color',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected $appends = ['banner_image_url', 'button_style', 'title_style', 'subtitle_style'];

    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('created_at', 'desc');
    }

    // Accessors
    public function getBannerImageUrlAttribute(): ?string
    {
        if (!$this->banner_image) {
            return null;
        }
        return asset('storage/' . $this->banner_image);
    }

    public function getButtonStyleAttribute(): array
    {
        return [
            'color' => $this->button_text_color,
            'background_color' => $this->button_bg_color,
            'style_string' => "color: {$this->button_text_color}; background-color: {$this->button_bg_color};",
        ];
    }

    public function getHasButtonAttribute(): bool
    {
        return !empty($this->button_text) && !empty($this->button_url);
    }

    public function getDisplayTitleAttribute(): string
    {
        return $this->title;
    }

    public function getDisplaySubtitleAttribute(): ?string
    {
        return $this->subtitle;
    }

    public function getTitleStyleAttribute(): array
    {
        return [
            'color' => $this->title_color,
            'style_string' => "color: {$this->title_color};",
        ];
    }

    public function getSubtitleStyleAttribute(): array
    {
        return [
            'color' => $this->subtitle_color,
            'style_string' => "color: {$this->subtitle_color};",
        ];
    }
}
