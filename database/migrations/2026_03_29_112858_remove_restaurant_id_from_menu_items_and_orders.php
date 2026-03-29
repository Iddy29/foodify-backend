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
        // Remove restaurant_id from orders
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['restaurant_id']);
            $table->dropColumn('restaurant_id');
        });

        // Remove restaurant_id from menu_items
        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropForeign(['restaurant_id']);
            $table->dropColumn('restaurant_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add restaurant_id back to menu_items
        Schema::table('menu_items', function (Blueprint $table) {
            $table->foreignId('restaurant_id')->constrained()->onDelete('cascade');
        });

        // Add restaurant_id back to orders
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('restaurant_id')->constrained()->onDelete('cascade');
        });
    }
};
