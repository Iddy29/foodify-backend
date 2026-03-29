<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Restaurant;
use App\Models\MenuItem;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@foodify.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'phone' => '+1234567890',
            'is_active' => true,
        ]);

        // Create a regular customer user
        $customer = User::create([
            'name' => 'Customer User',
            'email' => 'customer@foodify.com',
            'password' => Hash::make('password'),
            'role' => 'customer',
            'phone' => '+1987654321',
            'is_active' => true,
        ]);

        // Create categories
        $categories = [
            ['name' => 'Pizza', 'icon' => '🍕', 'sort_order' => 1],
            ['name' => 'Sushi', 'icon' => '🍣', 'sort_order' => 2],
            ['name' => 'Burgers', 'icon' => '🍔', 'sort_order' => 3],
            ['name' => 'Healthy', 'icon' => '🥗', 'sort_order' => 4],
            ['name' => 'Pasta', 'icon' => '🍝', 'sort_order' => 5],
            ['name' => 'Desserts', 'icon' => '🍰', 'sort_order' => 6],
            ['name' => 'Mexican', 'icon' => '🌮', 'sort_order' => 7],
            ['name' => 'Asian', 'icon' => '🥡', 'sort_order' => 8],
        ];

        foreach ($categories as $categoryData) {
            Category::create($categoryData);
        }

        // Create sample restaurant
        $restaurant = Restaurant::create([
            'name' => 'Bella Italia',
            'image' => 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?w=600&h=400&fit=crop',
            'cover_image' => 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=800&h=400&fit=crop',
            'rating' => 4.7,
            'review_count' => 324,
            'delivery_time' => '25-35 min',
            'delivery_fee' => 2.99,
            'distance' => '1.2 miles',
            'cuisine' => ['Italian', 'Pizza', 'Pasta'],
            'price_range' => '$$',
            'address' => '123 Italian Ave, Downtown',
            'description' => 'Authentic Italian cuisine with handmade pasta and wood-fired pizzas. Family recipes passed down through generations.',
            'featured' => true,
            'menu_categories' => ['Appetizers', 'Pizza', 'Pasta', 'Desserts'],
            'is_active' => true,
        ]);

        // Create sample menu items
        $menuItems = [
            [
                'name' => 'Margarita Pizza',
                'description' => 'Classic pizza with fresh basil, mozzarella, and tomato sauce on our signature thin crust.',
                'price' => 14.99,
                'image' => 'https://images.unsplash.com/photo-1574071318508-1cdbab80d002?w=400&h=300&fit=crop',
                'category' => 'Pizza',
                'ingredients' => ['Mozzarella', 'Fresh Basil', 'Tomato Sauce', 'Olive Oil'],
                'sizes' => [
                    ['name' => 'Small', 'price' => 11.99],
                    ['name' => 'Medium', 'price' => 14.99],
                    ['name' => 'Large', 'price' => 18.99],
                ],
                'popular' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Spaghetti Carbonara',
                'description' => 'Traditional carbonara with pancetta, egg, parmesan, and black pepper.',
                'price' => 15.99,
                'image' => 'https://images.unsplash.com/photo-1612874742237-6526221588e3?w=400&h=300&fit=crop',
                'category' => 'Pasta',
                'ingredients' => ['Spaghetti', 'Pancetta', 'Egg', 'Parmesan', 'Black Pepper'],
                'sizes' => [
                    ['name' => 'Small', 'price' => 12.99],
                    ['name' => 'Medium', 'price' => 15.99],
                    ['name' => 'Large', 'price' => 18.99],
                ],
                'popular' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Tiramisu',
                'description' => 'Classic Italian dessert with espresso-soaked ladyfingers, mascarpone cream, and cocoa.',
                'price' => 8.99,
                'image' => 'https://images.unsplash.com/photo-1571877227200-a0d98ea607e9?w=400&h=300&fit=crop',
                'category' => 'Desserts',
                'ingredients' => ['Mascarpone', 'Espresso', 'Ladyfingers', 'Cocoa', 'Eggs'],
                'sizes' => [
                    ['name' => 'Small', 'price' => 8.99],
                    ['name' => 'Medium', 'price' => 11.99],
                ],
                'popular' => false,
                'is_active' => true,
            ],
        ];

        foreach ($menuItems as $menuItemData) {
            $menuItemData['restaurant_id'] = $restaurant->id;
            MenuItem::create($menuItemData);
        }

        $this->command->info('Admin user created: admin@foodify.com / password');
        $this->command->info('Customer user created: customer@foodify.com / password');
        $this->command->info('Sample restaurant and menu items created.');
    }
}
