<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class DriverController extends Controller
{
    /**
     * List all drivers.
     *
     * GET /api/admin/drivers
     */
    public function index(Request $request): JsonResponse
    {
        $query = Driver::query()->with('user:id,name,email,phone');

        // Filter by approval status
        if ($request->has('is_approved')) {
            $query->where('is_approved', $request->boolean('is_approved'));
        }

        // Filter by online status
        if ($request->has('is_online')) {
            $query->where('is_online', $request->boolean('is_online'));
        }

        // Search by name, email, or vehicle number
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('vehicle_number', 'like', '%' . $search . '%')
                  ->orWhere('license_number', 'like', '%' . $search . '%')
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', '%' . $search . '%')
                         ->orWhere('email', 'like', '%' . $search . '%');
                  });
            });
        }

        $drivers = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json($drivers);
    }

    /**
     * Get pending drivers (approval queue).
     *
     * GET /api/admin/drivers/pending
     */
    public function pending(): JsonResponse
    {
        $drivers = Driver::with('user:id,name,email,phone')
            ->where('is_approved', false)
            ->whereNull('approved_at')
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($drivers);
    }

    /**
     * Get a single driver.
     *
     * GET /api/admin/drivers/{driver}
     */
    public function show(Driver $driver): JsonResponse
    {
        $driver->load(['user', 'approver', 'orders' => function ($q) {
            $q->latest()->limit(10);
        }]);

        return response()->json($driver);
    }

    /**
     * Approve a driver.
     *
     * PATCH /api/admin/drivers/{driver}/approve
     */
    public function approve(Request $request, Driver $driver): JsonResponse
    {
        if ($driver->is_approved) {
            return response()->json([
                'message' => 'Driver is already approved.',
            ], 422);
        }

        $driver->update([
            'is_approved' => true,
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
            'rejection_reason' => null,
        ]);

        // Notify driver
        // TODO: Send push notification to driver

        return response()->json([
            'message' => 'Driver approved successfully.',
            'driver' => $driver->fresh(['user']),
        ]);
    }

    /**
     * Reject a driver.
     *
     * PATCH /api/admin/drivers/{driver}/reject
     */
    public function reject(Request $request, Driver $driver): JsonResponse
    {
        try {
            $validated = $request->validate([
                'reason' => 'required|string|max:500',
            ]);

            if ($driver->is_approved) {
                return response()->json([
                    'message' => 'Cannot reject an approved driver.',
                ], 422);
            }

            $driver->update([
                'rejection_reason' => $validated['reason'],
                'approved_by' => $request->user()->id,
                'approved_at' => now(),
            ]);

            return response()->json([
                'message' => 'Driver rejected.',
                'driver' => $driver->fresh(['user']),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Suspend/revoke driver approval.
     *
     * PATCH /api/admin/drivers/{driver}/suspend
     */
    public function suspend(Request $request, Driver $driver): JsonResponse
    {
        if (!$driver->is_approved) {
            return response()->json([
                'message' => 'Driver is not approved.',
            ], 422);
        }

        $driver->update([
            'is_approved' => false,
            'is_online' => false,
        ]);

        // If driver has active order, unassign
        $activeOrder = $driver->orders()
            ->whereIn('status', ['accepted', 'preparing', 'out_for_delivery'])
            ->first();

        if ($activeOrder) {
            $activeOrder->update([
                'driver_id' => null,
                'status' => 'pending',
            ]);
        }

        return response()->json([
            'message' => 'Driver suspended.',
            'driver' => $driver->fresh(['user']),
        ]);
    }

    /**
     * Get driver statistics.
     *
     * GET /api/admin/drivers/stats
     */
    public function stats(): JsonResponse
    {
        $stats = [
            'total_drivers' => Driver::count(),
            'approved_drivers' => Driver::where('is_approved', true)->count(),
            'pending_drivers' => Driver::where('is_approved', false)->whereNull('rejection_reason')->count(),
            'online_drivers' => Driver::where('is_online', true)->where('is_approved', true)->count(),
            'rejected_drivers' => Driver::whereNotNull('rejection_reason')->count(),
        ];

        return response()->json($stats);
    }
}
