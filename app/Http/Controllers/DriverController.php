<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class DriverController extends Controller
{
    /**
     * Driver onboarding.
     *
     * POST /api/driver/onboard
     */
    public function onboard(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'vehicle_type' => 'required|in:bike,car,scooter',
                'vehicle_number' => 'required|string|max:20',
                'license_number' => 'required|string|max:50|unique:drivers',
                'phone' => 'required|string|max:20',
            ]);

            // Check if user already has a driver record
            $existingDriver = Driver::where('user_id', $request->user()->id)->first();
            if ($existingDriver) {
                return response()->json([
                    'message' => 'Driver record already exists.',
                    'driver' => $existingDriver,
                ], 422);
            }

            $driver = Driver::create([
                'user_id' => $request->user()->id,
                'vehicle_type' => $validated['vehicle_type'],
                'vehicle_number' => $validated['vehicle_number'],
                'license_number' => $validated['license_number'],
                'phone' => $validated['phone'],
                'is_approved' => false, // DEFAULT - Approval Gate
                'is_online' => false,
            ]);

            return response()->json([
                'message' => 'Driver registration submitted. Waiting for admin approval.',
                'driver' => $driver,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Get my driver profile.
     *
     * GET /api/driver/profile
     */
    public function profile(Request $request): JsonResponse
    {
        $driver = Driver::withCount([
            'orders as total_orders' => function ($q) {
                $q->where('status', 'delivered');
            },
            'orders as today_orders' => function ($q) {
                $q->where('status', 'delivered')
                  ->whereDate('delivered_at', today());
            }
        ])->where('user_id', $request->user()->id)->first();

        if (!$driver) {
            return response()->json([
                'message' => 'Driver not found. Please complete onboarding.',
            ], 404);
        }

        return response()->json($driver);
    }

    /**
     * Go online (start accepting orders).
     *
     * POST /api/driver/go-online
     */
    public function goOnline(Request $request): JsonResponse
    {
        $driver = Driver::where('user_id', $request->user()->id)->first();

        if (!$driver) {
            return response()->json([
                'message' => 'Driver not found.',
            ], 404);
        }

        // APPROVAL GATE
        if (!$driver->is_approved) {
            return response()->json([
                'message' => 'Not approved. Please wait for admin approval.',
                'is_approved' => false,
            ], 403);
        }

        try {
            $validated = $request->validate([
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
            ]);

            $driver->update([
                'is_online' => true,
                'current_latitude' => $validated['latitude'],
                'current_longitude' => $validated['longitude'],
                'last_location_at' => now(),
            ]);

            return response()->json([
                'message' => 'You are now online and can receive orders.',
                'driver' => $driver,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Go offline.
     *
     * POST /api/driver/go-offline
     */
    public function goOffline(Request $request): JsonResponse
    {
        $driver = Driver::where('user_id', $request->user()->id)->first();

        if (!$driver) {
            return response()->json([
                'message' => 'Driver not found.',
            ], 404);
        }

        // Check if driver has active order
        $activeOrder = $driver->orders()
            ->whereIn('status', ['accepted', 'preparing', 'out_for_delivery'])
            ->first();

        if ($activeOrder) {
            return response()->json([
                'message' => 'Cannot go offline while you have an active order.',
                'active_order' => $activeOrder,
            ], 422);
        }

        $driver->update([
            'is_online' => false,
        ]);

        return response()->json([
            'message' => 'You are now offline.',
            'driver' => $driver,
        ]);
    }

    /**
     * Update current location.
     *
     * POST /api/driver/location
     */
    public function updateLocation(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
                'accuracy' => 'nullable|numeric',
                'speed' => 'nullable|numeric',
                'heading' => 'nullable|numeric',
            ]);

            $driver = Driver::where('user_id', $request->user()->id)->first();

            if (!$driver) {
                return response()->json(['message' => 'Driver not found.'], 404);
            }

            // APPROVAL GATE
            if (!$driver->is_approved) {
                return response()->json(['message' => 'Not approved.'], 403);
            }

            $driver->update([
                'current_latitude' => $validated['latitude'],
                'current_longitude' => $validated['longitude'],
                'last_location_at' => now(),
            ]);

            // Update active order location if any
            $activeOrder = $driver->orders()
                ->where('status', 'out_for_delivery')
                ->first();

            if ($activeOrder) {
                $activeOrder->updateDriverLocation(
                    $validated['latitude'],
                    $validated['longitude']
                );
            }

            return response()->json(['message' => 'Location updated.']);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Get available orders.
     *
     * GET /api/driver/available-orders
     */
    public function availableOrders(Request $request): JsonResponse
    {
        $driver = Driver::where('user_id', $request->user()->id)->first();

        if (!$driver || !$driver->canAcceptOrders()) {
            return response()->json([
                'message' => 'You must be approved and online to view orders.',
            ], 403);
        }

        $orders = Order::with('user:id,name,phone')
            ->where('status', 'pending')
            ->whereNull('driver_id')
            ->orderBy('created_at', 'asc')
            ->limit(20)
            ->get();

        return response()->json($orders);
    }

    /**
     * Accept an order.
     *
     * POST /api/driver/orders/{order}/accept
     */
    public function acceptOrder(Request $request, Order $order): JsonResponse
    {
        $driver = Driver::where('user_id', $request->user()->id)->first();

        if (!$driver) {
            return response()->json(['message' => 'Driver not found.'], 404);
        }

        // APPROVAL GATE
        if (!$driver->canAcceptOrders()) {
            return response()->json([
                'message' => 'You must be approved and online to accept orders.',
                'is_approved' => $driver->is_approved,
                'is_online' => $driver->is_online,
            ], 403);
        }

        // Check if driver already has an active order
        $activeOrder = $driver->orders()
            ->whereIn('status', ['accepted', 'preparing', 'out_for_delivery'])
            ->first();

        if ($activeOrder) {
            return response()->json([
                'message' => 'You already have an active order.',
                'active_order' => $activeOrder,
            ], 422);
        }

        // Check order is still pending
        if ($order->status !== 'pending' || $order->driver_id !== null) {
            return response()->json([
                'message' => 'Order is no longer available.',
            ], 422);
        }

        // Accept the order
        $order->update([
            'driver_id' => $driver->id,
            'status' => 'accepted',
            'assigned_at' => now(),
            'accepted_at' => now(),
        ]);

        // Log status change
        $order->statusLogs()->create([
            'status_from' => 'pending',
            'status_to' => 'accepted',
            'changed_by' => $request->user()->id,
            'changed_at' => now(),
            'notes' => 'Driver accepted order',
        ]);

        return response()->json([
            'message' => 'Order accepted successfully.',
            'order' => $order->fresh(['user']),
        ]);
    }

    /**
     * Get my active order.
     *
     * GET /api/driver/active-order
     */
    public function activeOrder(Request $request): JsonResponse
    {
        $driver = Driver::where('user_id', $request->user()->id)->first();

        if (!$driver) {
            return response()->json(['message' => 'Driver not found.'], 404);
        }

        $order = $driver->orders()
            ->with('user:id,name,phone')
            ->whereIn('status', ['accepted', 'preparing', 'out_for_delivery'])
            ->latest()
            ->first();

        if (!$order) {
            return response()->json([
                'message' => 'No active order.',
                'order' => null,
            ]);
        }

        return response()->json($order);
    }

    /**
     * Get order history.
     *
     * GET /api/driver/order-history
     */
    public function orderHistory(Request $request): JsonResponse
    {
        $driver = Driver::where('user_id', $request->user()->id)->first();

        if (!$driver) {
            return response()->json(['message' => 'Driver not found.'], 404);
        }

        $orders = $driver->orders()
            ->with('user:id,name')
            ->whereIn('status', ['delivered', 'cancelled'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($orders);
    }
}
