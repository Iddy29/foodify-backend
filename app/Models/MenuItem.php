<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'image',
        'category',
        'ingredients',
        'sizes',
        'popular',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'ingredients' => 'array',
        'sizes' => 'array',
        'popular' => 'boolean',
        'is_active' => 'boolean',
    ];
}
