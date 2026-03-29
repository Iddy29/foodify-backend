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
     * Get all active restaurants with optional filtering.
     *
     * GET /api/restaurants
     */
    public function restaurants(Request $request): JsonResponse
    {
        $query = Restaurant::where('is_active', true);

        // Filter by featured
        if ($request->has('featured')) {
            $query->where('featured', true);
        }

        // Filter by category/cuisine
        if ($request->has('category')) {
            $category = $request->category;
            $query->where(function ($q) use ($category) {
                $q->whereJsonContains('cuisine', $category)
                  ->orWhereHas('menuItems', function ($mq) use ($category) {
                      $mq->where('category', 'like', '%' . $category . '%');
                  });
            });
        }

        // Search by name or cuisine
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhereJsonContains('cuisine', $search);
            });
        }

        $restaurants = $query
            ->withCount('menuItems')
            ->orderBy('featured', 'desc')
            ->orderBy('rating', 'desc')
            ->get([
                'id', 'name', 'image', 'cover_image', 'rating', 'review_count',
                'delivery_time', 'delivery_fee', 'distance', 'cuisine',
                'price_range', 'address', 'description', 'featured'
            ]);

        return response()->json($restaurants);
    }

    /**
     * Get a single restaurant with its menu items.
     *
     * GET /api/restaurants/{restaurant}
     */
    public function restaurant(Restaurant $restaurant): JsonResponse
    {
        if (! $restaurant->is_active) {
            return response()->json(['message' => 'Restaurant not found.'], 404);
        }

        $restaurant->load(['menuItems' => function ($query) {
            $query->where('is_active', true)
                ->orderBy('popular', 'desc')
                ->orderBy('category')
                ->orderBy('name');
        }]);

        return response()->json($restaurant);
    }

    /**
     * Get menu items for a specific restaurant.
     *
     * GET /api/restaurants/{restaurant}/menu-items
     */
    public function menuItems(Restaurant $restaurant): JsonResponse
    {
        if (! $restaurant->is_active) {
            return response()->json(['message' => 'Restaurant not found.'], 404);
        }

        $menuItems = $restaurant->menuItems()
            ->where('is_active', true)
            ->orderBy('popular', 'desc')
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        return response()->json($menuItems);
    }

    /**
     * Search across restaurants and menu items.
     *
     * GET /api/search
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->get('q', '');

        if (empty($query)) {
            return response()->json([
                'restaurants' => [],
                'menu_items' => [],
            ]);
        }

        $searchTerm = '%' . $query . '%';

        // Search restaurants
        $restaurants = Restaurant::where('is_active', true)
            ->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', $searchTerm)
                  ->orWhere('description', 'like', $searchTerm)
                  ->orWhereJsonContains('cuisine', str_replace('%', '', $searchTerm));
            })
            ->limit(20)
            ->get();

        // Search menu items
        $menuItems = MenuItem::where('is_active', true)
            ->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', $searchTerm)
                  ->orWhere('description', 'like', $searchTerm)
                  ->orWhere('category', 'like', $searchTerm);
            })
            ->with('restaurant:id,name,image')
            ->limit(20)
            ->get();

        return response()->json([
            'restaurants' => $restaurants,
            'menu_items' => $menuItems,
            'query' => $query,
        ]);
    }

    /**
     * Get featured restaurants.
     *
     * GET /api/featured-restaurants
     */
    public function featuredRestaurants(): JsonResponse
    {
        $restaurants = Restaurant::where('is_active', true)
            ->where('featured', true)
            ->withCount('menuItems')
            ->orderBy('rating', 'desc')
            ->limit(10)
            ->get();

        return response()->json($restaurants);
    }

    /**
     * Get popular restaurants.
     *
     * GET /api/popular-restaurants
     */
    public function popularRestaurants(): JsonResponse
    {
        $restaurants = Restaurant::where('is_active', true)
            ->withCount('menuItems')
            ->orderBy('rating', 'desc')
            ->orderBy('review_count', 'desc')
            ->limit(10)
            ->get();

        return response()->json($restaurants);
    }
}
