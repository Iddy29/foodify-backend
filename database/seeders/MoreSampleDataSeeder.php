<?php

namespace Database\Seeders;

use App\Models\MenuItem;
use Illuminate\Database\Seeder;

class MoreSampleDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Add more menu items to the single restaurant
        $menuItems = [
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
                'is_active' => true,
            ],
            [
                'name' => 'Dragon Roll',
                'description' => 'Shrimp tempura inside, topped with avocado, eel, and unagi sauce.',
                'price' => 16.99,
                'image' => 'https://images.unsplash.com/photo-1579871494447-9811cf80d66c?w=400&h=300&fit=crop',
                'category' => 'Sushi',
                'ingredients' => ['Shrimp Tempura', 'Avocado', 'Eel', 'Unagi Sauce', 'Rice', 'Nori'],
                'sizes' => [
                    ['name' => 'Small', 'price' => 12.99],
                    ['name' => 'Medium', 'price' => 16.99],
                    ['name' => 'Large', 'price' => 21.99],
                ],
                'popular' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Tonkotsu Ramen',
                'description' => 'Rich pork bone broth with chashu pork, soft-boiled egg, and fresh noodles.',
                'price' => 16.99,
                'image' => 'https://images.unsplash.com/photo-1569718212165-3a8278d5f624?w=400&h=300&fit=crop',
                'category' => 'Asian',
                'ingredients' => ['Pork Bone Broth', 'Chashu Pork', 'Soft-Boiled Egg', 'Noodles', 'Green Onion', 'Nori'],
                'sizes' => [
                    ['name' => 'Small', 'price' => 13.99],
                    ['name' => 'Medium', 'price' => 16.99],
                    ['name' => 'Large', 'price' => 19.99],
                ],
                'popular' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Tacos Al Pastor',
                'description' => 'Marinated pork with pineapple, cilantro, and onions on corn tortillas.',
                'price' => 11.99,
                'image' => 'https://images.unsplash.com/photo-1565299585323-38d6b0865b47?w=400&h=300&fit=crop',
                'category' => 'Mexican',
                'ingredients' => ['Marinated Pork', 'Pineapple', 'Cilantro', 'Onions', 'Corn Tortillas'],
                'sizes' => [
                    ['name' => 'Small', 'price' => 8.99],
                    ['name' => 'Medium', 'price' => 11.99],
                    ['name' => 'Large', 'price' => 14.99],
                ],
                'popular' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Acai Power Bowl',
                'description' => 'Acai blend topped with granola, banana, berries, coconut flakes, and honey.',
                'price' => 13.99,
                'image' => 'https://images.unsplash.com/photo-1590301157890-4810ed352733?w=400&h=300&fit=crop',
                'category' => 'Healthy',
                'ingredients' => ['Acai', 'Granola', 'Banana', 'Mixed Berries', 'Coconut Flakes', 'Honey'],
                'sizes' => [
                    ['name' => 'Small', 'price' => 10.99],
                    ['name' => 'Medium', 'price' => 13.99],
                    ['name' => 'Large', 'price' => 16.99],
                ],
                'popular' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Truffle Fries',
                'description' => 'Crispy golden fries tossed in truffle oil and parmesan cheese.',
                'price' => 7.99,
                'image' => 'https://images.unsplash.com/photo-1573080496219-bb080dd4f877?w=400&h=300&fit=crop',
                'category' => 'Appetizers',
                'ingredients' => ['Potatoes', 'Truffle Oil', 'Parmesan', 'Sea Salt'],
                'sizes' => [
                    ['name' => 'Small', 'price' => 5.99],
                    ['name' => 'Medium', 'price' => 7.99],
                    ['name' => 'Large', 'price' => 9.99],
                ],
                'popular' => false,
                'is_active' => true,
            ],
        ];

        foreach ($menuItems as $menuItemData) {
            MenuItem::create($menuItemData);
        }

        $this->command->info('Additional menu items created.');
    }
}
