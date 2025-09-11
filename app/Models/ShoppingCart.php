<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShoppingCart extends Model
{
    use HasFactory;

    protected $table = 'shopping_cart';

    protected $fillable = [
        'user_id',
        'medicine_id',
        'quantity',
        'unit_price',
        'total_price',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($cartItem) {
            $cartItem->total_price = $cartItem->quantity * $cartItem->unit_price;
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public static function addToCart($userId, $medicineId, $quantity = 1)
    {
        $medicine = Medicine::findOrFail($medicineId);
        
        $cartItem = static::where('user_id', $userId)
            ->where('medicine_id', $medicineId)
            ->first();

        if ($cartItem) {
            $cartItem->quantity += $quantity;
            $cartItem->save();
        } else {
            $cartItem = static::create([
                'user_id' => $userId,
                'medicine_id' => $medicineId,
                'quantity' => $quantity,
                'unit_price' => $medicine->cost,
            ]);
        }

        return $cartItem;
    }

    public static function getTotalForUser($userId)
    {
        return static::where('user_id', $userId)->sum('total_price');
    }

    public static function getItemCountForUser($userId)
    {
        return static::where('user_id', $userId)->sum('quantity');
    }
}
