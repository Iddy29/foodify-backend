<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CategoryController extends Controller
{
    /**
     * List all categories.
     *
     * GET /api/admin/categories
     */
    public function index(Request $request): JsonResponse
    {
        $query = Category::query();

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $categories = $query->orderBy('sort_order')->orderBy('name')->paginate(20);

        return response()->json($categories);
    }

    /**
     * Get all categories (for dropdowns).
     *
     * GET /api/admin/categories/all
     */
    public function all(): JsonResponse
    {
        $categories = Category::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'icon']);

        return response()->json($categories);
    }

    /**
     * Store a new category.
     *
     * POST /api/admin/categories
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:categories',
                'icon' => 'nullable|string|max:50',
                'image' => 'nullable|string|max:1000',
                'is_active' => 'boolean',
                'sort_order' => 'integer|min:0',
            ]);

            $category = Category::create($validated);

            return response()->json([
                'message' => 'Category created successfully.',
                'category' => $category,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Get a single category.
     *
     * GET /api/admin/categories/{id}
     */
    public function show(Category $category): JsonResponse
    {
        return response()->json($category);
    }

    /**
     * Update a category.
     *
     * PUT /api/admin/categories/{id}
     */
    public function update(Request $request, Category $category): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
                'icon' => 'nullable|string|max:50',
                'image' => 'nullable|string|max:1000',
                'is_active' => 'boolean',
                'sort_order' => 'integer|min:0',
            ]);

            $category->update($validated);

            return response()->json([
                'message' => 'Category updated successfully.',
                'category' => $category,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Delete a category.
     *
     * DELETE /api/admin/categories/{id}
     */
    public function destroy(Category $category): JsonResponse
    {
        $category->delete();

        return response()->json([
            'message' => 'Category deleted successfully.',
        ]);
    }

    /**
     * Toggle category active status.
     *
     * PATCH /api/admin/categories/{id}/toggle
     */
    public function toggle(Category $category): JsonResponse
    {
        $category->update(['is_active' => ! $category->is_active]);

        return response()->json([
            'message' => 'Category status updated.',
            'is_active' => $category->is_active,
        ]);
    }
}
