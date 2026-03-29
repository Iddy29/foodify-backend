<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\TrackingController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MenuItemController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\RestaurantController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\DriverController as AdminDriverController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Foodify Authentication API
|--------------------------------------------------------------------------
|
| All routes are prefixed with /api automatically by Laravel.
|
*/

Route::prefix('auth')->group(function () {
    // Public
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login',    [AuthController::class, 'login']);

    // Protected
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me',          [AuthController::class, 'me']);
        Route::post('/logout',     [AuthController::class, 'logout']);
        Route::post('/logout-all', [AuthController::class, 'logoutAll']);
    });
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

// Public tracking (no auth required)
Route::get('/track/{tracking_key}', [TrackingController::class, 'trackOrder']);

/*
|--------------------------------------------------------------------------
| Protected Customer Routes
|--------------------------------------------------------------------------
|
| These routes require authentication.
|
*/

Route::middleware('auth:sanctum')->group(function () {
    // Customer Orders
    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);
    Route::patch('/orders/{order}/cancel', [OrderController::class, 'cancel']);
    Route::get('/orders/{order}/tracking', [TrackingController::class, 'getTracking']);

    // Driver Onboarding & Profile
    Route::post('/driver/onboard', [DriverController::class, 'onboard']);
    Route::get('/driver/profile', [DriverController::class, 'profile']);
    
    // Driver Status (Online/Offline)
    Route::post('/driver/go-online', [DriverController::class, 'goOnline']);
    Route::post('/driver/go-offline', [DriverController::class, 'goOffline']);
    
    // Driver Location (O(1) updates)
    Route::post('/driver/location', [DriverController::class, 'updateLocation']);
    
    // Driver Orders
    Route::get('/driver/available-orders', [DriverController::class, 'availableOrders']);
    Route::post('/driver/orders/{order}/accept', [DriverController::class, 'acceptOrder']);
    Route::get('/driver/active-order', [DriverController::class, 'activeOrder']);
    Route::get('/driver/order-history', [DriverController::class, 'orderHistory']);
    Route::patch('/driver/orders/{order}/status', [OrderController::class, 'updateStatus']);
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
        Route::get('/orders/stats', [AdminOrderController::class, 'stats']);
        Route::get('/orders/recent', [AdminOrderController::class, 'recent']);
        Route::apiResource('orders', AdminOrderController::class);
        Route::patch('/orders/{order}/status', [AdminOrderController::class, 'updateStatus']);
        Route::get('/orders/{order}/location-history', [TrackingController::class, 'getLocationHistory']);
        Route::post('/orders/{order}/archive-tracking', [TrackingController::class, 'archiveTracking']);

        // Drivers (Approval System)
        Route::get('/drivers/stats', [AdminDriverController::class, 'stats']);
        Route::get('/drivers/pending', [AdminDriverController::class, 'pending']);
        Route::apiResource('drivers', AdminDriverController::class);
        Route::patch('/drivers/{driver}/approve', [AdminDriverController::class, 'approve']);
        Route::patch('/drivers/{driver}/reject', [AdminDriverController::class, 'reject']);
        Route::patch('/drivers/{driver}/suspend', [AdminDriverController::class, 'suspend']);
    });
