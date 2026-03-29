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
        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('vehicle_type')->default('bike'); // bike, car, scooter
            $table->string('vehicle_number');
            $table->string('license_number')->unique();
            $table->string('phone');
            
            // APPROVAL GATE - Default FALSE
            $table->boolean('is_approved')->default(false);
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            
            // Driver Status
            $table->boolean('is_online')->default(false);
            $table->decimal('current_latitude', 10, 8)->nullable();
            $table->decimal('current_longitude', 11, 8)->nullable();
            $table->timestamp('last_location_at')->nullable();
            
            // Stats
            $table->integer('total_deliveries')->default(0);
            $table->decimal('rating', 2, 1)->default(5.0);
            $table->integer('review_count')->default(0);
            
            $table->timestamps();
            
            $table->index(['is_approved', 'is_online']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drivers');
    }
};
