<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Restaurant;
use App\Models\MenuItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicController extends Controller
{
    /**
     * Get all active categories.
     *
     * GET /api/categories
     */
    public function categories(): JsonResponse
    {
        $categories = Category::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'icon', 'image']);

        return response()->json($categories);
    }

    /**
     * Get restaurant info.
     *
     * GET /api/restaurant
     */
    public function restaurant(): JsonResponse
    {
        $restaurant = Restaurant::first();

        if (! $restaurant) {
            return response()->json(['message' => 'Restaurant not configured.'], 404);
        }

        return response()->json($restaurant);
    }

    /**
     * Get menu items for the restaurant.
     *
     * GET /api/menu-items
     */
    public function menuItems(Request $request): JsonResponse
    {
        $query = MenuItem::where('is_active', true);

        // Filter by category
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        // Filter by popular
        if ($request->has('popular')) {
            $query->where('popular', true);
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%')
                  ->orWhere('category', 'like', '%' . $search . '%');
            });
        }

        $menuItems = $query
            ->orderBy('popular', 'desc')
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        return response()->json($menuItems);
    }

    /**
     * Get a single menu item.
     *
     * GET /api/menu-items/{menuItem}
     */
    public function menuItem(MenuItem $menuItem): JsonResponse
    {
        if (! $menuItem->is_active) {
            return response()->json(['message' => 'Menu item not found.'], 404);
        }

        return response()->json($menuItem);
    }

    /**
     * Search menu items.
     *
     * GET /api/search
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->get('q', '');

        if (empty($query)) {
            return response()->json([
                'menu_items' => [],
            ]);
        }

        $searchTerm = '%' . $query . '%';

        // Search menu items
        $menuItems = MenuItem::where('is_active', true)
            ->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', $searchTerm)
                  ->orWhere('description', 'like', $searchTerm)
                  ->orWhere('category', 'like', $searchTerm);
            })
            ->orderBy('popular', 'desc')
            ->orderBy('name')
            ->limit(20)
            ->get();

        return response()->json([
            'menu_items' => $menuItems,
            'query' => $query,
        ]);
    }

    /**
     * Get popular menu items.
     *
     * GET /api/popular-items
     */
    public function popularItems(): JsonResponse
    {
        $items = MenuItem::where('is_active', true)
            ->where('popular', true)
            ->orderBy('name')
            ->limit(10)
            ->get();

        return response()->json($items);
    }
}
