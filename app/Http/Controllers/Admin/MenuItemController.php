<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class MenuItemController extends Controller
{
    /**
     * Store uploaded image and return the path.
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
     * List all menu items.
     *
     * GET /api/admin/menu-items
     */
    public function index(Request $request): JsonResponse
    {
        $query = MenuItem::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
            });
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

        $menuItems = $query->orderBy('category')
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
                'name' => 'required|string|max:255',
                'description' => 'required|string|max:1000',
                'price' => 'required|numeric|min:0',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
                'category' => 'required|string|max:100',
                'ingredients' => 'nullable|array',
                'ingredients.*' => 'string|max:100',
                'sizes' => 'nullable|array',
                'sizes.*.name' => 'required|string|max:50',
                'sizes.*.price' => 'required|numeric|min:0',
                'popular' => 'boolean',
                'is_active' => 'boolean',
            ]);

            // Handle image upload
            if ($request->hasFile('image')) {
                $validated['image'] = $this->storeImage($request->file('image'), 'menu-items');
            }

            $menuItem = MenuItem::create($validated);

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
                'name' => 'required|string|max:255',
                'description' => 'required|string|max:1000',
                'price' => 'required|numeric|min:0',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
                'category' => 'required|string|max:100',
                'ingredients' => 'nullable|array',
                'ingredients.*' => 'string|max:100',
                'sizes' => 'nullable|array',
                'sizes.*.name' => 'required|string|max:50',
                'sizes.*.price' => 'required|numeric|min:0',
                'popular' => 'boolean',
                'is_active' => 'boolean',
            ]);

            // Handle image upload
            if ($request->hasFile('image')) {
                // Delete old image
                $this->deleteOldImage($menuItem->image);
                $validated['image'] = $this->storeImage($request->file('image'), 'menu-items');
            }

            $menuItem->update($validated);

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
        // Delete associated image
        $this->deleteOldImage($menuItem->image);
        
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
}
