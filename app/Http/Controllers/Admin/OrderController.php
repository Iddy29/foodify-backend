<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    /**
     * List all orders.
     *
     * GET /api/admin/orders
     */
    public function index(Request $request): JsonResponse
    {
        $query = Order::query()->with(['user:id,name,email', 'restaurant:id,name']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by user
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by restaurant
        if ($request->has('restaurant_id')) {
            $query->where('restaurant_id', $request->restaurant_id);
        }

        // Search by order number
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', '%' . $search . '%')
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', '%' . $search . '%')
                         ->orWhere('email', 'like', '%' . $search . '%');
                  });
            });
        }

        // Date range filter
        if ($request->has('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->has('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json($orders);
    }

    /**
     * Get order statistics.
     *
     * GET /api/admin/orders/stats
     */
    public function stats(): JsonResponse
    {
        $today = now()->startOfDay();
        $thisMonth = now()->startOfMonth();

        $stats = [
            'total_orders' => Order::count(),
            'today_orders' => Order::whereDate('created_at', $today)->count(),
            'month_orders' => Order::whereDate('created_at', '>=', $thisMonth)->count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'confirmed_orders' => Order::where('status', 'confirmed')->count(),
            'preparing_orders' => Order::where('status', 'preparing')->count(),
            'on_the_way_orders' => Order::where('status', 'on_the_way')->count(),
            'delivered_orders' => Order::where('status', 'delivered')->count(),
            'cancelled_orders' => Order::where('status', 'cancelled')->count(),
            'total_revenue' => Order::where('status', '!=', 'cancelled')->sum('total'),
            'today_revenue' => Order::where('status', '!=', 'cancelled')
                ->whereDate('created_at', $today)
                ->sum('total'),
        ];

        return response()->json($stats);
    }

    /**
     * Get a single order.
     *
     * GET /api/admin/orders/{order}
     */
    public function show(Order $order): JsonResponse
    {
        $order->load(['user:id,name,email,phone', 'restaurant:id,name,address,phone']);

        return response()->json($order);
    }

    /**
     * Update order status.
     *
     * PATCH /api/admin/orders/{order}/status
     */
    public function updateStatus(Request $request, Order $order): JsonResponse
    {
        try {
            $validated = $request->validate([
                'status' => 'required|in:pending,confirmed,preparing,on_the_way,delivered,cancelled',
                'estimated_delivery' => 'nullable|date',
            ]);

            $updateData = ['status' => $validated['status']];

            if (!empty($validated['estimated_delivery'])) {
                $updateData['estimated_delivery'] = $validated['estimated_delivery'];
            }

            // Set delivered_at when status is delivered
            if ($validated['status'] === 'delivered' && $order->status !== 'delivered') {
                $updateData['delivered_at'] = now();
            }

            $order->update($updateData);

            return response()->json([
                'message' => 'Order status updated successfully.',
                'order' => $order->fresh(['user:id,name,email', 'restaurant:id,name']),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Delete an order.
     *
     * DELETE /api/admin/orders/{order}
     */
    public function destroy(Order $order): JsonResponse
    {
        $order->delete();

        return response()->json([
            'message' => 'Order deleted successfully.',
        ]);
    }

    /**
     * Get recent orders.
     *
     * GET /api/admin/orders/recent
     */
    public function recent(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 10);

        $orders = Order::with(['user:id,name,email', 'restaurant:id,name'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        return response()->json($orders);
    }
}
