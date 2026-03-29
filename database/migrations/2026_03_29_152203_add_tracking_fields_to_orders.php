<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Driver Assignment
            $table->foreignId('driver_id')->nullable()->constrained('drivers')->after('user_id');
            $table->timestamp('assigned_at')->nullable()->after('driver_id');
            $table->timestamp('accepted_at')->nullable()->after('assigned_at');
            $table->timestamp('picked_up_at')->nullable()->after('accepted_at');
            $table->timestamp('delivered_at')->nullable()->after('picked_up_at');
            
            // Tracking
            $table->string('tracking_key')->unique()->nullable()->after('status');
            $table->decimal('driver_latitude', 10, 8)->nullable();
            $table->decimal('driver_longitude', 11, 8)->nullable();
            $table->timestamp('location_updated_at')->nullable();
            
            // Estimated Arrival
            $table->timestamp('estimated_arrival')->nullable();
            
            // Extend status enum
            // Note: If using enum, you may need to modify the existing migration
            // For now, using string with validation in the model
        });
        
        // Update status column to allow new statuses
        DB::statement("ALTER TABLE orders MODIFY status VARCHAR(30) DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['driver_id']);
            $table->dropColumn([
                'driver_id', 'assigned_at', 'accepted_at', 'picked_up_at', 'delivered_at',
                'tracking_key', 'driver_latitude', 'driver_longitude', 'location_updated_at',
                'estimated_arrival'
            ]);
        });
    }
};
