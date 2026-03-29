<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Driver extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'vehicle_type',
        'vehicle_number',
        'license_number',
        'phone',
        'is_approved',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'is_online',
        'current_latitude',
        'current_longitude',
        'last_location_at',
        'total_deliveries',
        'rating',
        'review_count',
    ];

    protected $casts = [
        'is_approved' => 'boolean',
        'is_online' => 'boolean',
        'approved_at' => 'datetime',
        'last_location_at' => 'datetime',
        'current_latitude' => 'decimal:8',
        'current_longitude' => 'decimal:8',
        'rating' => 'decimal:1',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function locationHistory(): HasMany
    {
        return $this->hasMany(LocationHistory::class);
    }

    /**
     * Check if driver can accept orders (Approval Gate)
     */
    public function canAcceptOrders(): bool
    {
        return $this->is_approved && $this->is_online;
    }
}
