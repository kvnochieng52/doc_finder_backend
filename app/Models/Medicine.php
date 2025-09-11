<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Medicine extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'medicine_number',
        'cost',
        'image',
        'category_id',
        'subcategory_id',
        'conditions',
        'manufacturer',
        'strength',
        'form',
        'quantity_available',
        'is_active',
        'requires_prescription',
        'sort_order',
    ];

    protected $casts = [
        'cost' => 'decimal:2',
        'conditions' => 'array',
        'quantity_available' => 'integer',
        'is_active' => 'boolean',
        'requires_prescription' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($medicine) {
            if (empty($medicine->slug)) {
                $medicine->slug = Str::slug($medicine->name);
            }
        });

        static::updating(function ($medicine) {
            if ($medicine->isDirty('name') && empty($medicine->slug)) {
                $medicine->slug = Str::slug($medicine->name);
            }
        });
    }

    public function category()
    {
        return $this->belongsTo(MedicineCategory::class, 'category_id');
    }

    public function subcategory()
    {
        return $this->belongsTo(MedicineSubcategory::class, 'subcategory_id');
    }

    public function cartItems()
    {
        return $this->hasMany(ShoppingCart::class, 'medicine_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInStock($query)
    {
        return $query->where('quantity_available', '>', 0);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%")
              ->orWhere('manufacturer', 'like', "%{$search}%")
              ->orWhere('medicine_number', 'like', "%{$search}%")
              ->orWhereJsonContains('conditions', $search);
        });
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeBySubcategory($query, $subcategoryId)
    {
        return $query->where('subcategory_id', $subcategoryId);
    }

    public function getImageUrlAttribute()
    {
        if (!$this->image) {
            return null;
        }

        if (str_starts_with($this->image, 'http')) {
            return $this->image;
        }

        return 'http://69.30.235.220:8006/storage/' . ltrim($this->image, '/');
    }
}
