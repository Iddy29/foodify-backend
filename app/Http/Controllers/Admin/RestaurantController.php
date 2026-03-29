<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class RestaurantController extends Controller
{
    /**
     * List all restaurants.
     *
     * GET /api/admin/restaurants
     */
    public function index(Request $request): JsonResponse
    {
        $query = Restaurant::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('address', 'like', '%' . $search . '%')
                  ->orWhereJsonContains('cuisine', $search);
            });
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->has('featured')) {
            $query->where('featured', $request->boolean('featured'));
        }

        $restaurants = $query->withCount('menuItems')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($restaurants);
    }

    /**
     * Get all restaurants (for dropdowns).
     *
     * GET /api/admin/restaurants/all
     */
    public function all(): JsonResponse
    {
        $restaurants = Restaurant::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'image']);

        return response()->json($restaurants);
    }

    /**
     * Store a new restaurant.
     *
     * POST /api/admin/restaurants
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'image' => 'nullable|string|max:1000',
                'cover_image' => 'nullable|string|max:1000',
                'rating' => 'nullable|numeric|min:0|max:5',
                'review_count' => 'nullable|integer|min:0',
                'delivery_time' => 'required|string|max:50',
                'delivery_fee' => 'nullable|numeric|min:0',
                'distance' => 'nullable|string|max:50',
                'cuisine' => 'required|array',
                'cuisine.*' => 'string|max:50',
                'price_range' => 'nullable|string|max:10',
                'address' => 'required|string|max:500',
                'description' => 'required|string|max:2000',
                'featured' => 'boolean',
                'menu_categories' => 'nullable|array',
                'menu_categories.*' => 'string|max:50',
                'is_active' => 'boolean',
            ]);

            $restaurant = Restaurant::create($validated);

            return response()->json([
                'message' => 'Restaurant created successfully.',
                'restaurant' => $restaurant,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Get a single restaurant.
     *
     * GET /api/admin/restaurants/{id}
     */
    public function show(Restaurant $restaurant): JsonResponse
    {
        $restaurant->load(['menuItems' => function ($query) {
            $query->orderBy('category')->orderBy('name');
        }]);

        return response()->json($restaurant);
    }

    /**
     * Update a restaurant.
     *
     * PUT /api/admin/restaurants/{id}
     */
    public function update(Request $request, Restaurant $restaurant): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'image' => 'nullable|string|max:1000',
                'cover_image' => 'nullable|string|max:1000',
                'rating' => 'nullable|numeric|min:0|max:5',
                'review_count' => 'nullable|integer|min:0',
                'delivery_time' => 'required|string|max:50',
                'delivery_fee' => 'nullable|numeric|min:0',
                'distance' => 'nullable|string|max:50',
                'cuisine' => 'required|array',
                'cuisine.*' => 'string|max:50',
                'price_range' => 'nullable|string|max:10',
                'address' => 'required|string|max:500',
                'description' => 'required|string|max:2000',
                'featured' => 'boolean',
                'menu_categories' => 'nullable|array',
                'menu_categories.*' => 'string|max:50',
                'is_active' => 'boolean',
            ]);

            $restaurant->update($validated);

            return response()->json([
                'message' => 'Restaurant updated successfully.',
                'restaurant' => $restaurant,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Delete a restaurant.
     *
     * DELETE /api/admin/restaurants/{id}
     */
    public function destroy(Restaurant $restaurant): JsonResponse
    {
        $restaurant->delete();

        return response()->json([
            'message' => 'Restaurant deleted successfully.',
        ]);
    }

    /**
     * Toggle restaurant active status.
     *
     * PATCH /api/admin/restaurants/{id}/toggle
     */
    public function toggle(Restaurant $restaurant): JsonResponse
    {
        $restaurant->update(['is_active' => ! $restaurant->is_active]);

        return response()->json([
            'message' => 'Restaurant status updated.',
            'is_active' => $restaurant->is_active,
        ]);
    }

    /**
     * Toggle restaurant featured status.
     *
     * PATCH /api/admin/restaurants/{id}/toggle-featured
     */
    public function toggleFeatured(Restaurant $restaurant): JsonResponse
    {
        $restaurant->update(['featured' => ! $restaurant->featured]);

        return response()->json([
            'message' => 'Restaurant featured status updated.',
            'featured' => $restaurant->featured,
        ]);
    }
}
