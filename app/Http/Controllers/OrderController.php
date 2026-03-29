<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Driver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    /**
     * Get customer's orders.
     *
     * GET /api/orders
     */
    public function index(Request $request): JsonResponse
    {
        $query = Order::with(['driver.user:id,name'])
            ->where('user_id', $request->user()->id);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json($orders);
    }

    /**
     * Get a single order.
     *
     * GET /api/orders/{order}
     */
    public function show(Request $request, Order $order): JsonResponse
    {
        // Check order belongs to user
        if ($order->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $order->load(['driver.user:id,name,phone', 'statusLogs.changer:id,name']);

        return response()->json($order);
    }

    /**
     * Create a new order.
     *
     * POST /api/orders
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'items' => 'required|array',
                'items.*.menuItem.id' => 'required|integer',
                'items.*.menuItem.name' => 'required|string',
                'items.*.menuItem.price' => 'required|numeric',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.selectedSize.name' => 'required|string',
                'items.*.selectedSize.price' => 'required|numeric',
                'subtotal' => 'required|numeric|min:0',
                'delivery_fee' => 'required|numeric|min:0',
                'tax' => 'required|numeric|min:0',
                'total' => 'required|numeric|min:0',
                'delivery_address' => 'required|string',
                'payment_method' => 'required|string',
                'special_instructions' => 'nullable|string',
            ]);

            $order = Order::create([
                'user_id' => $request->user()->id,
                'items' => $validated['items'],
                'subtotal' => $validated['subtotal'],
                'delivery_fee' => $validated['delivery_fee'],
                'tax' => $validated['tax'],
                'total' => $validated['total'],
                'status' => Order::STATUS_PENDING,
                'delivery_address' => $validated['delivery_address'],
                'payment_method' => $validated['payment_method'],
                'special_instructions' => $validated['special_instructions'] ?? null,
            ]);

            // Log initial status
            $order->statusLogs()->create([
                'status_from' => null,
                'status_to' => Order::STATUS_PENDING,
                'changed_by' => $request->user()->id,
                'changed_at' => now(),
                'notes' => 'Order created',
            ]);

            return response()->json([
                'message' => 'Order created successfully.',
                'order' => $order->fresh(),
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Update order status (for driver).
     *
     * PATCH /api/driver/orders/{order}/status
     */
    public function updateStatus(Request $request, Order $order): JsonResponse
    {
        try {
            $validated = $request->validate([
                'status' => 'required|in:preparing,ready_for_pickup,out_for_delivery,delivered',
                'notes' => 'nullable|string',
            ]);

            $driver = Driver::where('user_id', $request->user()->id)->first();

            if (!$driver) {
                return response()->json(['message' => 'Driver not found.'], 404);
            }

            // Verify driver owns this order
            if ($order->driver_id !== $driver->id) {
                return response()->json(['message' => 'Not your order.'], 403);
            }

            // Transition status
            $order->transitionStatus(
                $validated['status'],
                $request->user()->id,
                $validated['notes'] ?? null
            );

            // If delivered, archive tracking and update driver stats
            if ($validated['status'] === Order::STATUS_DELIVERED) {
                // Archive location history
                $this->archiveOrderTracking($order);
                
                // Update driver stats
                $driver->increment('total_deliveries');
                $driver->update(['is_online' => true]); // Ready for next order
            }

            return response()->json([
                'message' => 'Order status updated.',
                'order' => $order->fresh(),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Cancel order (customer only, before acceptance).
     *
     * PATCH /api/orders/{order}/cancel
     */
    public function cancel(Request $request, Order $order): JsonResponse
    {
        if ($order->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        if (!in_array($order->status, [Order::STATUS_PENDING, Order::STATUS_ACCEPTED])) {
            return response()->json([
                'message' => 'Order cannot be cancelled at this stage.',
            ], 422);
        }

        $order->transitionStatus(
            Order::STATUS_CANCELLED,
            $request->user()->id,
            'Cancelled by customer'
        );

        return response()->json([
            'message' => 'Order cancelled.',
            'order' => $order->fresh(),
        ]);
    }

    /**
     * Archive order tracking data.
     */
    private function archiveOrderTracking(Order $order): void
    {
        if (!$order->driver_id) return;

        // Archive final location
        \App\Models\LocationHistory::create([
            'order_id' => $order->id,
            'driver_id' => $order->driver_id,
            'latitude' => $order->driver_latitude ?? $order->driver->current_latitude,
            'longitude' => $order->driver_longitude ?? $order->driver->current_longitude,
            'recorded_at' => $order->location_updated_at ?? now(),
        ]);

        // Clear temporary tracking data
        $order->update([
            'driver_latitude' => null,
            'driver_longitude' => null,
            'location_updated_at' => null,
        ]);
    }
}
