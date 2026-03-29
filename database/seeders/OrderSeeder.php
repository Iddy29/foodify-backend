<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\User;
use App\Models\Restaurant;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::where('role', 'customer')->get();
        $restaurants = Restaurant::all();

        if ($users->isEmpty() || $restaurants->isEmpty()) {
            $this->command->info('No users or restaurants found. Skipping order seeding.');
            return;
        }

        $statuses = ['pending', 'confirmed', 'preparing', 'on_the_way', 'delivered', 'cancelled'];

        foreach ($users as $user) {
            // Create 2-4 orders per user
            $orderCount = rand(2, 4);
            
            for ($i = 0; $i < $orderCount; $i++) {
                $restaurant = $restaurants->random();
                $status = $statuses[array_rand($statuses)];
                
                // Create order items from restaurant menu
                $menuItems = $restaurant->menuItems()->take(3)->get();
                $items = [];
                $subtotal = 0;

                foreach ($menuItems as $menuItem) {
                    $quantity = rand(1, 3);
                    $sizePrice = is_array($menuItem->sizes) && count($menuItem->sizes) > 0 
                        ? $menuItem->sizes[0]['price'] 
                        : $menuItem->price;
                    
                    $items[] = [
                        'menuItem' => [
                            'id' => $menuItem->id,
                            'name' => $menuItem->name,
                            'price' => $menuItem->price,
                            'image' => $menuItem->image,
                        ],
                        'quantity' => $quantity,
                        'selectedSize' => [
                            'name' => is_array($menuItem->sizes) && count($menuItem->sizes) > 0 
                                ? $menuItem->sizes[0]['name'] 
                                : 'Regular',
                            'price' => $sizePrice,
                        ],
                        'specialInstructions' => rand(0, 1) ? 'Extra spicy please' : null,
                    ];
                    
                    $subtotal += $sizePrice * $quantity;
                }

                $deliveryFee = $restaurant->delivery_fee;
                $tax = $subtotal * 0.08; // 8% tax
                $total = $subtotal + $deliveryFee + $tax;

                $createdAt = now()->subDays(rand(0, 30))->subHours(rand(0, 23));

                Order::create([
                    'user_id' => $user->id,
                    'restaurant_id' => $restaurant->id,
                    'order_number' => 'ORD-' . strtoupper(uniqid()),
                    'items' => $items,
                    'subtotal' => $subtotal,
                    'delivery_fee' => $deliveryFee,
                    'tax' => $tax,
                    'total' => $total,
                    'status' => $status,
                    'delivery_address' => $user->address ?? '123 Main St, City, State 12345',
                    'payment_method' => ['credit_card', 'paypal', 'cash'][array_rand(['credit_card', 'paypal', 'cash'])],
                    'special_instructions' => rand(0, 1) ? 'Please call when arrived' : null,
                    'estimated_delivery' => $status !== 'pending' ? $createdAt->copy()->addMinutes(45) : null,
                    'delivered_at' => $status === 'delivered' ? $createdAt->copy()->addMinutes(rand(30, 60)) : null,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
            }
        }

        $this->command->info('Sample orders created successfully.');
    }
}
