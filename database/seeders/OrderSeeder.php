<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Fulfillment;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Str;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get some real products to use for seeding
        $products = Product::with('variants.pricing')->where('is_active', true)->take(5)->get();
        
        if ($products->isEmpty()) {
            return;
        }

        // Ideally we'd get a real user ID from the auth service, 
        // but for seeding we can use a placeholder UUID that matches the admin if needed.
        // admin@socia.com usually has a static UUID in many dev environments or we can just pick one.
        $customerId = '12345678-1234-1234-1234-1234567890ab'; 

        $statuses = ['PENDING', 'CONFIRMED', 'PROCESSING', 'SHIPPED', 'DELIVERED'];
        $paymentStatuses = ['UNPAID', 'PAID'];

        for ($i = 0; $i < 10; $i++) {
            $status = $statuses[array_rand($statuses)];
            $paymentStatus = ($status === 'DELIVERED' || $status === 'SHIPPED') ? 'PAID' : $paymentStatuses[array_rand($paymentStatuses)];
            
            $order = Order::create([
                'order_number' => 'ORD-' . strtoupper(Str::random(10)),
                'customer_id' => $customerId,
                'source' => 'WEBSITE',
                'status' => $status,
                'payment_status' => $paymentStatus,
                'payment_method' => 'GCASH',
                'subtotal' => 0,
                'shipping_fee' => 50,
                'total_amount' => 0,
                'created_at' => now()->subDays(rand(0, 30)),
            ]);

            $subtotal = 0;
            $itemsCount = rand(1, 3);
            
            for ($j = 0; $j < $itemsCount; $j++) {
                $product = $products->random();
                $variant = $product->variants->first();
                $pricing = $variant->pricing->where('tier', 'RETAIL')->first();
                $price = $pricing ? $pricing->price : 100;
                $qty = rand(1, 2);

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'variant_id' => $variant->id,
                    'product_name' => $product->name,
                    'variant_name' => $variant->name,
                    'sku' => $variant->sku,
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'total_price' => $price * $qty,
                ]);

                $subtotal += ($price * $qty);
            }

            $order->update([
                'subtotal' => $subtotal,
                'total_amount' => $subtotal + 50,
            ]);

            if ($status === 'SHIPPED' || $status === 'DELIVERED') {
                Fulfillment::create([
                    'order_id' => $order->id,
                    'courier_name' => 'J&T Express',
                    'tracking_number' => 'JT' . rand(1000000000, 9999999999),
                    'shipped_at' => $order->created_at->addDays(1),
                    'estimated_delivery' => $order->created_at->addDays(4),
                    'delivered_at' => $status === 'DELIVERED' ? $order->created_at->addDays(4) : null,
                ]);
            }
        }
    }
}
