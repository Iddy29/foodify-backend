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
        Schema::create('location_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('driver_id')->constrained()->onDelete('cascade');
            
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->float('accuracy')->nullable(); // GPS accuracy in meters
            $table->float('speed')->nullable(); // km/h
            $table->float('heading')->nullable(); // degrees
            
            $table->timestamp('recorded_at');
            $table->timestamps();
            
            // Partition-friendly indexing
            $table->index(['order_id', 'recorded_at']);
            $table->index(['driver_id', 'recorded_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('location_history');
    }
};
