<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use App\Models\Restaurant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class MenuItemController extends Controller
{
    /**
     * List all menu items.
     *
     * GET /api/admin/menu-items
     */
    public function index(Request $request): JsonResponse
    {
        $query = MenuItem::query()->with('restaurant:id,name');

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        if ($request->has('restaurant_id')) {
            $query->where('restaurant_id', $request->restaurant_id);
        }

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->has('popular')) {
            $query->where('popular', $request->boolean('popular'));
        }

        $menuItems = $query->orderBy('restaurant_id')
            ->orderBy('category')
            ->orderBy('name')
            ->paginate(20);

        return response()->json($menuItems);
    }

    /**
     * Store a new menu item.
     *
     * POST /api/admin/menu-items
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'restaurant_id' => 'required|exists:restaurants,id',
                'name' => 'required|string|max:255',
                'description' => 'required|string|max:1000',
                'price' => 'required|numeric|min:0',
                'image' => 'nullable|string|max:1000',
                'category' => 'required|string|max:100',
                'ingredients' => 'nullable|array',
                'ingredients.*' => 'string|max:100',
                'sizes' => 'nullable|array',
                'sizes.*.name' => 'required|string|max:50',
                'sizes.*.price' => 'required|numeric|min:0',
                'popular' => 'boolean',
                'is_active' => 'boolean',
            ]);

            $menuItem = MenuItem::create($validated);
            $menuItem->load('restaurant:id,name');

            return response()->json([
                'message' => 'Menu item created successfully.',
                'menu_item' => $menuItem,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Get a single menu item.
     *
     * GET /api/admin/menu-items/{id}
     */
    public function show(MenuItem $menuItem): JsonResponse
    {
        $menuItem->load('restaurant:id,name');

        return response()->json($menuItem);
    }

    /**
     * Update a menu item.
     *
     * PUT /api/admin/menu-items/{id}
     */
    public function update(Request $request, MenuItem $menuItem): JsonResponse
    {
        try {
            $validated = $request->validate([
                'restaurant_id' => 'required|exists:restaurants,id',
                'name' => 'required|string|max:255',
                'description' => 'required|string|max:1000',
                'price' => 'required|numeric|min:0',
                'image' => 'nullable|string|max:1000',
                'category' => 'required|string|max:100',
                'ingredients' => 'nullable|array',
                'ingredients.*' => 'string|max:100',
                'sizes' => 'nullable|array',
                'sizes.*.name' => 'required|string|max:50',
                'sizes.*.price' => 'required|numeric|min:0',
                'popular' => 'boolean',
                'is_active' => 'boolean',
            ]);

            $menuItem->update($validated);
            $menuItem->load('restaurant:id,name');

            return response()->json([
                'message' => 'Menu item updated successfully.',
                'menu_item' => $menuItem,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Delete a menu item.
     *
     * DELETE /api/admin/menu-items/{id}
     */
    public function destroy(MenuItem $menuItem): JsonResponse
    {
        $menuItem->delete();

        return response()->json([
            'message' => 'Menu item deleted successfully.',
        ]);
    }

    /**
     * Toggle menu item active status.
     *
     * PATCH /api/admin/menu-items/{id}/toggle
     */
    public function toggle(MenuItem $menuItem): JsonResponse
    {
        $menuItem->update(['is_active' => ! $menuItem->is_active]);

        return response()->json([
            'message' => 'Menu item status updated.',
            'is_active' => $menuItem->is_active,
        ]);
    }

    /**
     * Toggle menu item popular status.
     *
     * PATCH /api/admin/menu-items/{id}/toggle-popular
     */
    public function togglePopular(MenuItem $menuItem): JsonResponse
    {
        $menuItem->update(['popular' => ! $menuItem->popular]);

        return response()->json([
            'message' => 'Menu item popular status updated.',
            'popular' => $menuItem->popular,
        ]);
    }

    /**
     * Get menu items by restaurant.
     *
     * GET /api/admin/restaurants/{restaurant}/menu-items
     */
    public function byRestaurant(Restaurant $restaurant): JsonResponse
    {
        $menuItems = $restaurant->menuItems()
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        return response()->json($menuItems);
    }
}
