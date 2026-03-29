<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'driver_id',
        'order_number',
        'items',
        'subtotal',
        'delivery_fee',
        'tax',
        'total',
        'status',
        'delivery_address',
        'payment_method',
        'special_instructions',
        'estimated_delivery',
        'delivered_at',
        'tracking_key',
        'assigned_at',
        'accepted_at',
        'picked_up_at',
        'driver_latitude',
        'driver_longitude',
        'location_updated_at',
        'estimated_arrival',
    ];

    protected $casts = [
        'items' => 'array',
        'subtotal' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'estimated_delivery' => 'datetime',
        'delivered_at' => 'datetime',
        'assigned_at' => 'datetime',
        'accepted_at' => 'datetime',
        'picked_up_at' => 'datetime',
        'location_updated_at' => 'datetime',
        'estimated_arrival' => 'datetime',
        'driver_latitude' => 'decimal:8',
        'driver_longitude' => 'decimal:8',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_PREPARING = 'preparing';
    const STATUS_READY_FOR_PICKUP = 'ready_for_pickup';
    const STATUS_OUT_FOR_DELIVERY = 'out_for_delivery';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_CANCELLED = 'cancelled';

    // Valid status transitions
    const VALID_TRANSITIONS = [
        self::STATUS_PENDING => [self::STATUS_ACCEPTED, self::STATUS_CANCELLED],
        self::STATUS_ACCEPTED => [self::STATUS_PREPARING, self::STATUS_CANCELLED],
        self::STATUS_PREPARING => [self::STATUS_READY_FOR_PICKUP],
        self::STATUS_READY_FOR_PICKUP => [self::STATUS_OUT_FOR_DELIVERY],
        self::STATUS_OUT_FOR_DELIVERY => [self::STATUS_DELIVERED, self::STATUS_CANCELLED],
        self::STATUS_DELIVERED => [],
        self::STATUS_CANCELLED => [],
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = 'ORD-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6));
            }
            if (empty($order->tracking_key)) {
                $order->tracking_key = Str::random(32);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(OrderStatusLog::class);
    }

    public function locationHistory(): HasMany
    {
        return $this->hasMany(LocationHistory::class);
    }

    /**
     * Check if status can be transitioned to new status
     */
    public function canTransitionTo(string $newStatus): bool
    {
        return in_array($newStatus, self::VALID_TRANSITIONS[$this->status] ?? []);
    }

    /**
     * Transition order status
     */
    public function transitionStatus(string $newStatus, ?int $changedBy = null, ?string $notes = null): bool
    {
        if (!$this->canTransitionTo($newStatus)) {
            throw new \InvalidArgumentException(
                "Cannot transition from {$this->status} to {$newStatus}"
            );
        }

        $oldStatus = $this->status;
        $this->status = $newStatus;
        
        // Update timestamps based on status
        switch ($newStatus) {
            case self::STATUS_ACCEPTED:
                $this->accepted_at = now();
                break;
            case self::STATUS_OUT_FOR_DELIVERY:
                $this->picked_up_at = now();
                break;
            case self::STATUS_DELIVERED:
                $this->delivered_at = now();
                break;
        }
        
        $this->save();

        // Log the status change
        $this->statusLogs()->create([
            'status_from' => $oldStatus,
            'status_to' => $newStatus,
            'changed_by' => $changedBy,
            'notes' => $notes,
        ]);

        return true;
    }

    /**
     * Update driver location (O(1) operation)
     */
    public function updateDriverLocation(float $latitude, float $longitude): void
    {
        $this->update([
            'driver_latitude' => $latitude,
            'driver_longitude' => $longitude,
            'location_updated_at' => now(),
        ]);
    }

    /**
     * Check if order is trackable
     */
    public function isTrackable(): bool
    {
        return in_array($this->status, [
            self::STATUS_ACCEPTED,
            self::STATUS_PREPARING,
            self::STATUS_READY_FOR_PICKUP,
            self::STATUS_OUT_FOR_DELIVERY,
        ]);
    }

    /**
     * Check if order is active (not completed or cancelled)
     */
    public function isActive(): bool
    {
        return !in_array($this->status, [self::STATUS_DELIVERED, self::STATUS_CANCELLED]);
    }
}
