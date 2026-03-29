<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MenuItemController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\RestaurantController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Foodify Authentication API
|--------------------------------------------------------------------------
|
| All routes are prefixed with /api automatically by Laravel.
|
| Public routes (no token required):
|   POST /api/auth/register
|   POST /api/auth/login
|
| Protected routes (Bearer token required):
|   GET  /api/auth/me
|   POST /api/auth/logout
|   POST /api/auth/logout-all
|
*/

Route::prefix('auth')->group(function () {

    // ── Public ──────────────────────────────────────────────────────────
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login',    [AuthController::class, 'login']);

    // ── Protected ───────────────────────────────────────────────────────
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me',          [AuthController::class, 'me']);
        Route::post('/logout',     [AuthController::class, 'logout']);
        Route::post('/logout-all', [AuthController::class, 'logoutAll']);
    });
});

/*
|--------------------------------------------------------------------------
| Admin API Routes
|--------------------------------------------------------------------------
|
| All admin routes require authentication and admin role.
| Prefix: /api/admin
|
*/

Route::prefix('admin')
    ->middleware(['auth:sanctum', 'admin'])
    ->group(function () {

        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'stats']);

        // Categories
        Route::get('/categories/all', [CategoryController::class, 'all']);
        Route::apiResource('categories', CategoryController::class);
        Route::patch('/categories/{category}/toggle', [CategoryController::class, 'toggle']);

        // Restaurant Settings (Single Restaurant)
        Route::get('/restaurant', [RestaurantController::class, 'show']);
        Route::post('/restaurant', [RestaurantController::class, 'store']);
        Route::put('/restaurant', [RestaurantController::class, 'update']);
        Route::patch('/restaurant/toggle', [RestaurantController::class, 'toggle']);

        // Menu Items
        Route::apiResource('menu-items', MenuItemController::class);
        Route::patch('/menu-items/{menuItem}/toggle', [MenuItemController::class, 'toggle']);
        Route::patch('/menu-items/{menuItem}/toggle-popular', [MenuItemController::class, 'togglePopular']);

        // Users
        Route::apiResource('users', UserController::class);
        Route::patch('/users/{user}/toggle', [UserController::class, 'toggle']);

        // Orders
        Route::get('/orders/stats', [OrderController::class, 'stats']);
        Route::get('/orders/recent', [OrderController::class, 'recent']);
        Route::apiResource('orders', OrderController::class);
        Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus']);
    });

/*
|--------------------------------------------------------------------------
| Public API Routes
|--------------------------------------------------------------------------
|
| These routes are publicly accessible for the mobile app.
|
*/

Route::get('/categories', [PublicController::class, 'categories']);
Route::get('/restaurant', [PublicController::class, 'restaurant']);
Route::get('/menu-items', [PublicController::class, 'menuItems']);
Route::get('/menu-items/{menuItem}', [PublicController::class, 'menuItem']);
Route::get('/popular-items', [PublicController::class, 'popularItems']);
Route::get('/search', [PublicController::class, 'search']);
