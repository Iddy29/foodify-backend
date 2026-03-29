<?php

namespace Database\Seeders;

use App\Models\Restaurant;
use App\Models\MenuItem;
use Illuminate\Database\Seeder;

class MoreSampleDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Burger Joint
        $burgerJoint = Restaurant::create([
            'name' => 'Burger Joint',
            'image' => 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=600&h=400&fit=crop',
            'cover_image' => 'https://images.unsplash.com/photo-1466978913421-dad2ebd01d17?w=800&h=400&fit=crop',
            'rating' => 4.8,
            'review_count' => 512,
            'delivery_time' => '25-35 min',
            'delivery_fee' => 1.99,
            'distance' => '0.8 miles',
            'cuisine' => ['American', 'Burgers', 'Fast Food'],
            'price_range' => '$$',
            'address' => '456 Burger Blvd, Midtown',
            'description' => 'Premium handcrafted burgers made with 100% Angus beef. Crispy fries and creamy shakes complete the experience.',
            'featured' => true,
            'menu_categories' => ['Burgers', 'Sides', 'Drinks', 'Desserts'],
            'is_active' => true,
        ]);

        $this->createMenuItems($burgerJoint->id, [
            [
                'name' => 'Classic Smash Burger',
                'description' => 'Double smashed patties with American cheese, pickles, onions, and special sauce.',
                'price' => 12.99,
                'image' => 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=400&h=300&fit=crop',
                'category' => 'Burgers',
                'ingredients' => ['Angus Beef', 'American Cheese', 'Pickles', 'Onions', 'Special Sauce', 'Brioche Bun'],
                'sizes' => [
                    ['name' => 'Small', 'price' => 9.99],
                    ['name' => 'Medium', 'price' => 12.99],
                    ['name' => 'Large', 'price' => 15.99],
                ],
                'popular' => true,
            ],
            [
                'name' => 'Truffle Fries',
                'description' => 'Crispy golden fries tossed in truffle oil and parmesan cheese.',
                'price' => 7.99,
                'image' => 'https://images.unsplash.com/photo-1573080496219-bb080dd4f877?w=400&h=300&fit=crop',
                'category' => 'Sides',
                'ingredients' => ['Potatoes', 'Truffle Oil', 'Parmesan', 'Sea Salt'],
                'sizes' => [
                    ['name' => 'Small', 'price' => 5.99],
                    ['name' => 'Medium', 'price' => 7.99],
                    ['name' => 'Large', 'price' => 9.99],
                ],
                'popular' => false,
            ],
        ]);

        // Create Sakura Sushi
        $sakuraSushi = Restaurant::create([
            'name' => 'Sakura Sushi',
            'image' => 'https://images.unsplash.com/photo-1579871494447-9811cf80d66c?w=600&h=400&fit=crop',
            'cover_image' => 'https://images.unsplash.com/photo-1617196034183-421b4917c92d?w=800&h=400&fit=crop',
            'rating' => 4.9,
            'review_count' => 287,
            'delivery_time' => '30-40 min',
            'delivery_fee' => 3.99,
            'distance' => '2.1 miles',
            'cuisine' => ['Japanese', 'Sushi', 'Asian'],
            'price_range' => '$$$',
            'address' => '789 Sakura Lane, East Side',
            'description' => 'Premium sushi and Japanese cuisine prepared by master chefs. Fresh fish delivered daily from Tsukiji market.',
            'featured' => true,
            'menu_categories' => ['Sushi Rolls', 'Sashimi', 'Ramen', 'Sides', 'Drinks'],
            'is_active' => true,
        ]);

        $this->createMenuItems($sakuraSushi->id, [
            [
                'name' => 'Dragon Roll',
                'description' => 'Shrimp tempura inside, topped with avocado, eel, and unagi sauce.',
                'price' => 16.99,
                'image' => 'https://images.unsplash.com/photo-1579871494447-9811cf80d66c?w=400&h=300&fit=crop',
                'category' => 'Sushi Rolls',
                'ingredients' => ['Shrimp Tempura', 'Avocado', 'Eel', 'Unagi Sauce', 'Rice', 'Nori'],
                'sizes' => [
                    ['name' => 'Small', 'price' => 12.99],
                    ['name' => 'Medium', 'price' => 16.99],
                    ['name' => 'Large', 'price' => 21.99],
                ],
                'popular' => true,
            ],
            [
                'name' => 'Tonkotsu Ramen',
                'description' => 'Rich pork bone broth with chashu pork, soft-boiled egg, and fresh noodles.',
                'price' => 16.99,
                'image' => 'https://images.unsplash.com/photo-1569718212165-3a8278d5f624?w=400&h=300&fit=crop',
                'category' => 'Ramen',
                'ingredients' => ['Pork Bone Broth', 'Chashu Pork', 'Soft-Boiled Egg', 'Noodles', 'Green Onion', 'Nori'],
                'sizes' => [
                    ['name' => 'Small', 'price' => 13.99],
                    ['name' => 'Medium', 'price' => 16.99],
                    ['name' => 'Large', 'price' => 19.99],
                ],
                'popular' => true,
            ],
        ]);

        // Create Taco Fiesta
        $tacoFiesta = Restaurant::create([
            'name' => 'Taco Fiesta',
            'image' => 'https://images.unsplash.com/photo-1565299585323-38d6b0865b47?w=600&h=400&fit=crop',
            'cover_image' => 'https://images.unsplash.com/photo-1504544750208-dc0358e63f7f?w=800&h=400&fit=crop',
            'rating' => 4.5,
            'review_count' => 256,
            'delivery_time' => '20-30 min',
            'delivery_fee' => 1.99,
            'distance' => '0.5 miles',
            'cuisine' => ['Mexican', 'Tacos', 'Burritos'],
            'price_range' => '$',
            'address' => '555 Fiesta Rd, South Side',
            'description' => 'Vibrant Mexican street food with authentic recipes. From tacos al pastor to loaded burritos.',
            'featured' => false,
            'menu_categories' => ['Tacos', 'Burritos', 'Sides', 'Drinks'],
            'is_active' => true,
        ]);

        $this->createMenuItems($tacoFiesta->id, [
            [
                'name' => 'Tacos Al Pastor',
                'description' => 'Marinated pork with pineapple, cilantro, and onions on corn tortillas.',
                'price' => 11.99,
                'image' => 'https://images.unsplash.com/photo-1565299585323-38d6b0865b47?w=400&h=300&fit=crop',
                'category' => 'Tacos',
                'ingredients' => ['Marinated Pork', 'Pineapple', 'Cilantro', 'Onions', 'Corn Tortillas'],
                'sizes' => [
                    ['name' => 'Small', 'price' => 8.99],
                    ['name' => 'Medium', 'price' => 11.99],
                    ['name' => 'Large', 'price' => 14.99],
                ],
                'popular' => true,
            ],
        ]);

        // Create Green Bowl
        $greenBowl = Restaurant::create([
            'name' => 'Green Bowl',
            'image' => 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=600&h=400&fit=crop',
            'cover_image' => 'https://images.unsplash.com/photo-1498837167922-ddd27525d352?w=800&h=400&fit=crop',
            'rating' => 4.6,
            'review_count' => 198,
            'delivery_time' => '20-30 min',
            'delivery_fee' => 2.49,
            'distance' => '1.5 miles',
            'cuisine' => ['Healthy', 'Salads', 'Bowls'],
            'price_range' => '$$',
            'address' => '321 Health St, Westside',
            'description' => 'Fresh, nutritious bowls and salads made with locally sourced organic ingredients. Fuel your body the right way.',
            'featured' => false,
            'menu_categories' => ['Bowls', 'Salads', 'Smoothies', 'Snacks'],
            'is_active' => true,
        ]);

        $this->createMenuItems($greenBowl->id, [
            [
                'name' => 'Acai Power Bowl',
                'description' => 'Acai blend topped with granola, banana, berries, coconut flakes, and honey.',
                'price' => 13.99,
                'image' => 'https://images.unsplash.com/photo-1590301157890-4810ed352733?w=400&h=300&fit=crop',
                'category' => 'Bowls',
                'ingredients' => ['Acai', 'Granola', 'Banana', 'Mixed Berries', 'Coconut Flakes', 'Honey'],
                'sizes' => [
                    ['name' => 'Small', 'price' => 10.99],
                    ['name' => 'Medium', 'price' => 13.99],
                    ['name' => 'Large', 'price' => 16.99],
                ],
                'popular' => true,
            ],
        ]);

        $this->command->info('Additional sample restaurants created.');
    }

    private function createMenuItems(int $restaurantId, array $items): void
    {
        foreach ($items as $itemData) {
            $itemData['restaurant_id'] = $restaurantId;
            MenuItem::create($itemData);
        }
    }
}
