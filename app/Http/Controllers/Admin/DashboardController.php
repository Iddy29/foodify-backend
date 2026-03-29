<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\MenuItem;
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
        $stats = [
            'total_users' => User::count(),
            'total_customers' => User::where('role', 'customer')->count(),
            'total_admins' => User::where('role', 'admin')->count(),
            'total_restaurants' => Restaurant::count(),
            'total_menu_items' => MenuItem::count(),
            'total_categories' => Category::count(),
            'active_restaurants' => Restaurant::where('is_active', true)->count(),
            'featured_restaurants' => Restaurant::where('featured', true)->count(),
            'recent_users' => User::latest()->take(5)->get(['id', 'name', 'email', 'role', 'created_at']),
            'recent_restaurants' => Restaurant::latest()->take(5)->get(['id', 'name', 'rating', 'is_active', 'created_at']),
        ];

        return response()->json($stats);
    }
}
