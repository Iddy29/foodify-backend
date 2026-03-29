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
        Schema::create('restaurants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('image')->nullable();
            $table->string('cover_image')->nullable();
            $table->decimal('rating', 2, 1)->default(5.0);
            $table->integer('review_count')->default(0);
            $table->string('delivery_time');
            $table->decimal('delivery_fee', 8, 2)->default(0);
            $table->string('distance')->nullable();
            $table->json('cuisine'); // array of cuisine types
            $table->string('price_range')->default('$$');
            $table->text('address');
            $table->text('description');
            $table->boolean('featured')->default(false);
            $table->json('menu_categories')->nullable(); // array of menu category names
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('restaurants');
    }
};
