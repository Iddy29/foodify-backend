<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    /**
     * Get dashboard statistics.
     *
     * GET /api/admin/dashboard
     */
    public function stats(): JsonResponse
    {
        $today = now()->startOfDay();

        $stats = [
            'total_users' => User::count(),
            'total_customers' => User::where('role', 'customer')->count(),
            'total_admins' => User::where('role', 'admin')->count(),
            'total_menu_items' => MenuItem::count(),
            'active_menu_items' => MenuItem::where('is_active', true)->count(),
            'total_categories' => Category::count(),
            'active_categories' => Category::where('is_active', true)->count(),
            'total_orders' => Order::count(),
            'today_orders' => Order::whereDate('created_at', $today)->count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'total_revenue' => Order::where('status', '!=', 'cancelled')->sum('total'),
            'today_revenue' => Order::where('status', '!=', 'cancelled')
                ->whereDate('created_at', $today)
                ->sum('total'),
            'recent_users' => User::latest()->take(5)->get(['id', 'name', 'email', 'role', 'created_at']),
            'recent_orders' => Order::with('user:id,name,email')
                ->latest()
                ->take(5)
                ->get(['id', 'order_number', 'user_id', 'total', 'status', 'created_at']),
            'restaurant' => Restaurant::first(),
        ];

        return response()->json($stats);
    }
}
