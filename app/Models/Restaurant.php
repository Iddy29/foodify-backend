<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Restaurant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'image',
        'cover_image',
        'rating',
        'review_count',
        'delivery_time',
        'delivery_fee',
        'distance',
        'cuisine',
        'price_range',
        'address',
        'description',
        'featured',
        'menu_categories',
        'is_active',
    ];

    protected $casts = [
        'rating' => 'decimal:1',
        'delivery_fee' => 'decimal:2',
        'cuisine' => 'array',
        'menu_categories' => 'array',
        'featured' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function menuItems(): HasMany
    {
        return $this->hasMany(MenuItem::class);
    }
}
