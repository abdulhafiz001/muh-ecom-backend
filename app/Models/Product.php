<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'sale_price',
        'stock_quantity',
        'sku',
        'images',
        'specifications',
        'is_active',
        'is_featured',
        'category_id'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'stock_quantity' => 'integer',
        'images' => 'array',
        'specifications' => 'array',
        'is_active' => 'boolean',
        'is_featured' => 'boolean'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    public function getCurrentPriceAttribute()
    {
        return $this->sale_price ?? $this->price;
    }

    public function getIsOnSaleAttribute()
    {
        return !is_null($this->sale_price) && $this->sale_price < $this->price;
    }

    public function getImagesAttribute($value)
    {
        if (!$value) {
            return [];
        }
        
        $images = is_string($value) ? json_decode($value, true) : $value;
        
        if (!is_array($images)) {
            return [];
        }
        
        // Ensure all image URLs are full URLs
        return array_map(function($image) {
            if (str_starts_with($image, 'http')) {
                return $image;
            }
            if (str_starts_with($image, '/storage/')) {
                return asset($image);
            }
            return asset('storage/' . $image);
        }, $images);
    }
}
