<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class RestaurantController extends Controller
{
    /**
     * Store uploaded image and return the URL.
     */
    private function storeImage(?\Illuminate\Http\UploadedFile $file, string $directory): ?string
    {
        if (!$file) {
            return null;
        }

        $path = $file->store($directory, 'public');
        return Storage::url($path);
    }

    /**
     * Delete old image if exists.
     */
    private function deleteOldImage(?string $imageUrl): void
    {
        if ($imageUrl) {
            $path = str_replace('/storage/', '', parse_url($imageUrl, PHP_URL_PATH) ?? $imageUrl);
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }
    }

    /**
     * Get the restaurant (single restaurant app).
     *
     * GET /api/admin/restaurant
     */
    public function show(): JsonResponse
    {
        $restaurant = Restaurant::first();

        if (! $restaurant) {
            return response()->json(['message' => 'Restaurant not configured.'], 404);
        }

        return response()->json($restaurant);
    }

    /**
     * Create the restaurant (if not exists).
     *
     * POST /api/admin/restaurant
     */
    public function store(Request $request): JsonResponse
    {
        // Check if restaurant already exists
        if (Restaurant::exists()) {
            return response()->json([
                'message' => 'Restaurant already exists. Use PUT to update.',
            ], 422);
        }

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
                'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
                'rating' => 'nullable|numeric|min:0|max:5',
                'review_count' => 'nullable|integer|min:0',
                'delivery_time' => 'required|string|max:50',
                'delivery_fee' => 'nullable|numeric|min:0',
                'distance' => 'nullable|string|max:50',
                'cuisine' => 'nullable|array',
                'cuisine.*' => 'string|max:50',
                'price_range' => 'nullable|string|max:10',
                'address' => 'required|string|max:500',
                'description' => 'required|string|max:2000',
                'featured' => 'boolean',
                'menu_categories' => 'nullable|array',
                'menu_categories.*' => 'string|max:50',
                'is_active' => 'boolean',
            ]);

            // Handle image uploads
            if ($request->hasFile('image')) {
                $validated['image'] = $this->storeImage($request->file('image'), 'restaurants');
            }
            if ($request->hasFile('cover_image')) {
                $validated['cover_image'] = $this->storeImage($request->file('cover_image'), 'restaurants/covers');
            }

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
     * Update the restaurant.
     *
     * PUT /api/admin/restaurant
     */
    public function update(Request $request): JsonResponse
    {
        $restaurant = Restaurant::first();

        if (! $restaurant) {
            return response()->json(['message' => 'Restaurant not found.'], 404);
        }

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
                'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
                'rating' => 'nullable|numeric|min:0|max:5',
                'review_count' => 'nullable|integer|min:0',
                'delivery_time' => 'required|string|max:50',
                'delivery_fee' => 'nullable|numeric|min:0',
                'distance' => 'nullable|string|max:50',
                'cuisine' => 'nullable|array',
                'cuisine.*' => 'string|max:50',
                'price_range' => 'nullable|string|max:10',
                'address' => 'required|string|max:500',
                'description' => 'required|string|max:2000',
                'featured' => 'boolean',
                'menu_categories' => 'nullable|array',
                'menu_categories.*' => 'string|max:50',
                'is_active' => 'boolean',
            ]);

            // Handle image uploads
            if ($request->hasFile('image')) {
                $this->deleteOldImage($restaurant->image);
                $validated['image'] = $this->storeImage($request->file('image'), 'restaurants');
            }
            if ($request->hasFile('cover_image')) {
                $this->deleteOldImage($restaurant->cover_image);
                $validated['cover_image'] = $this->storeImage($request->file('cover_image'), 'restaurants/covers');
            }

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
     * Toggle restaurant active status.
     *
     * PATCH /api/admin/restaurant/toggle
     */
    public function toggle(): JsonResponse
    {
        $restaurant = Restaurant::first();

        if (! $restaurant) {
            return response()->json(['message' => 'Restaurant not found.'], 404);
        }

        $restaurant->update(['is_active' => ! $restaurant->is_active]);

        return response()->json([
            'message' => 'Restaurant status updated.',
            'is_active' => $restaurant->is_active,
        ]);
    }
}
