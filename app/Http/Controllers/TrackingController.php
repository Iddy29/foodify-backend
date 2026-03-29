<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\LocationHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TrackingController extends Controller
{
    /**
     * Get order tracking info (for customer).
     *
     * GET /api/track/{tracking_key}
     */
    public function trackOrder(string $trackingKey): JsonResponse
    {
        $order = Order::with(['driver.user:id,name', 'driver:id,user_id,phone,current_latitude,current_longitude'])
            ->where('tracking_key', $trackingKey)
            ->first();

        if (!$order) {
            return response()->json([
                'message' => 'Order not found.',
            ], 404);
        }

        return response()->json([
            'order_number' => $order->order_number,
            'status' => $order->status,
            'is_trackable' => $order->isTrackable(),
            'driver' => $order->driver ? [
                'name' => $order->driver->user->name,
                'phone' => $this->maskPhone($order->driver->phone),
                'latitude' => $order->driver_latitude ?? $order->driver->current_latitude,
                'longitude' => $order->driver_longitude ?? $order->driver->current_longitude,
                'location_updated_at' => $order->location_updated_at,
            ] : null,
            'delivery_address' => $order->delivery_address,
            'estimated_arrival' => $order->estimated_arrival,
            'created_at' => $order->created_at,
        ]);
    }

    /**
     * Get my order tracking (authenticated customer).
     *
     * GET /api/orders/{order}/tracking
     */
    public function getTracking(Request $request, Order $order): JsonResponse
    {
        // Check order belongs to user
        if ($order->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        return response()->json([
            'order_number' => $order->order_number,
            'status' => $order->status,
            'is_trackable' => $order->isTrackable(),
            'driver' => $order->driver ? [
                'name' => $order->driver->user->name,
                'phone' => $this->maskPhone($order->driver->phone),
                'latitude' => $order->driver_latitude ?? $order->driver->current_latitude,
                'longitude' => $order->driver_longitude ?? $order->driver->current_longitude,
                'location_updated_at' => $order->location_updated_at,
            ] : null,
            'delivery_address' => $order->delivery_address,
            'estimated_arrival' => $order->estimated_arrival,
            'created_at' => $order->created_at,
        ]);
    }

    /**
     * Archive location history for completed order.
     * Called when order is marked as delivered.
     *
     * POST /api/admin/orders/{order}/archive-tracking
     */
    public function archiveTracking(Request $request, Order $order): JsonResponse
    {
        if ($order->status !== 'delivered') {
            return response()->json([
                'message' => 'Order must be delivered to archive.',
            ], 422);
        }

        if (!$order->driver_id) {
            return response()->json([
                'message' => 'No driver assigned to this order.',
            ], 422);
        }

        // Archive final location
        LocationHistory::create([
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

        return response()->json([
            'message' => 'Tracking data archived successfully.',
        ]);
    }

    /**
     * Get location history for an order (admin only).
     *
     * GET /api/admin/orders/{order}/location-history
     */
    public function getLocationHistory(Request $request, Order $order): JsonResponse
    {
        $history = $order->locationHistory()
            ->orderBy('recorded_at', 'asc')
            ->get();

        return response()->json($history);
    }

    /**
     * Mask phone number for privacy.
     */
    private function maskPhone(string $phone): string
    {
        $length = strlen($phone);
        if ($length <= 4) {
            return $phone;
        }
        return str_repeat('*', $length - 4) . substr($phone, -4);
    }
}
