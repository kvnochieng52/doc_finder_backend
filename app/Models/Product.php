<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_name',
        'product_description',
        'product_location',
        'product_tags',
        'product_featured_image',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get all images for the product
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class, 'product_id', 'id');
    }

    /**
     * Get the featured image for the product
     */
    public function featuredImage(): HasMany
    {
        return $this->hasMany(ProductImage::class, 'product_id', 'id')->where('is_featured', true);
    }

    /**
     * Get the user who created the product
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    /**
     * Get the user who last updated the product
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }

    /**
     * Scope to get products by specific user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('created_by', $userId);
    }

    /**
     * Scope to get products with images
     */
    public function scopeWithImages($query)
    {
        return $query->with(['images' => function ($query) {
            $query->orderBy('is_featured', 'desc')
                ->orderBy('created_at', 'asc');
        }]);
    }

    /**
     * Get the product's featured image URL
     */
    public function getFeaturedImageUrlAttribute(): ?string
    {
        if ($this->product_featured_image) {
            return \Storage::disk('public')->url($this->product_featured_image);
        }
        return null;
    }
}
