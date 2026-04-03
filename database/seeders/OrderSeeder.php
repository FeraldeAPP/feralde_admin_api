<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Fulfillment;
use App\Models\Product;
use App\Models\DistributorProfile;

use Illuminate\Support\Str;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get real products to use for seeding
        $products = Product::with('variants.pricing')->where('is_active', true)->take(8)->get();

        if ($products->isEmpty()) {
            $this->command->warn('OrderSeeder: No active products found. Skipping order seeding.');
            return;
        }

        $paymentMethods = ['GCASH', 'MAYA', 'BANK_TRANSFER', 'COD', 'CREDIT_CARD'];
        $cities = ['Makati', 'Quezon City', 'Cebu City', 'Davao City', 'Taguig', 'Pasig', 'Mandaluyong', 'Parañaque'];

        // ─────────────────────────────────────────
        // SECTION 1: SHOP ORDERS (customer-facing)
        // ─────────────────────────────────────────
        $shopSources = ['WEBSITE', 'TIKTOK_SHOP', 'LAZADA', 'ADMIN'];
        $shopCustomers = [
            ['id' => 'usr-cust-001', 'name' => 'Emily White'],
            ['id' => 'usr-cust-002', 'name' => 'Marco Reyes'],
            ['id' => 'usr-cust-003', 'name' => 'Sofia Andrade'],
            ['id' => 'usr-cust-004', 'name' => 'James Tan'],
            ['id' => 'usr-cust-005', 'name' => 'Anika Santos'],
            ['id' => 'usr-cust-006', 'name' => 'Rafael Cruz'],
            ['id' => 'usr-cust-007', 'name' => 'Camille Lim'],
            ['id' => 'usr-cust-008', 'name' => 'Diego Villanueva'],
            ['id' => 'usr-cust-009', 'name' => 'Maya Flores'],
            ['id' => 'usr-cust-010', 'name' => 'Leon Pascual'],
        ];

        /**
         * Shop order status groups distributed across all 4 Kanban columns:
         *   NEW       -> PENDING
         *   PREPARING -> CONFIRMED, PROCESSING
         *   PACKED    -> PACKED
         *   LOGISTICS -> SHIPPED
         */
        $shopStatusGroups = [
            ['PENDING',    'UNPAID',  [0, 2]],
            ['PENDING',    'UNPAID',  [0, 1]],
            ['PENDING',    'PAID',    [1, 3]],
            ['CONFIRMED',  'PAID',    [1, 4]],
            ['CONFIRMED',  'UNPAID',  [2, 5]],
            ['PROCESSING', 'PAID',    [2, 5]],
            ['PROCESSING', 'PAID',    [3, 6]],
            ['PACKED',     'PAID',    [3, 7]],
            ['PACKED',     'PAID',    [4, 8]],
            ['PACKED',     'PAID',    [4, 8]],
            ['SHIPPED',    'PAID',    [5, 10]],
            ['SHIPPED',    'PAID',    [6, 12]],
            ['SHIPPED',    'PAID',    [7, 14]],
            ['DELIVERED',  'PAID',    [10, 20]],
            ['DELIVERED',  'PAID',    [12, 25]],
        ];

        foreach ($shopStatusGroups as $i => [$status, $paymentStatus, $daysRange]) {
            $customer   = $shopCustomers[$i % count($shopCustomers)];
            $city       = $cities[array_rand($cities)];
            $daysAgo    = rand($daysRange[0], $daysRange[1]);
            $createdAt  = now()->subDays($daysAgo)->subHours(rand(0, 23));
            $payMethod  = $paymentMethods[array_rand($paymentMethods)];
            $source     = $shopSources[array_rand($shopSources)];

            $nameParts  = explode(' ', $customer['name']);
            $firstName  = $nameParts[0];
            $lastName   = $nameParts[1] ?? '';

            $address = \App\Models\Address::create([
                'user_id'     => $customer['id'],
                'first_name'  => $firstName,
                'last_name'   => $lastName,
                'phone'       => '09' . rand(100000000, 999999999),
                'region'      => 'NCR',
                'province'    => 'Metro Manila',
                'city'        => $city,
                'barangay'    => 'Barangay ' . rand(1, 100),
                'details'     => rand(1, 999) . ' ' . collect(['Rizal Ave', 'Mabini St', 'Del Pilar St', 'Magsaysay Blvd', 'Quezon Ave'])->random(),
                'postal_code' => (string) rand(1000, 1800),
                'country'     => 'PH',
                'is_default'  => true,
            ]);

            $order = Order::create([
                'order_number'        => 'SHP-' . strtoupper(Str::random(8)),
                'customer_id'         => $customer['id'],
                'distributor_id'      => null,
                'reseller_id'         => null,
                'source'              => $source,
                'status'              => $status,
                'payment_status'      => $paymentStatus,
                'payment_method'      => $payMethod,
                'shipping_address_id' => $address->id,
                'subtotal'            => 0,
                'shipping_fee'        => 50,
                'discount_amount'     => 0,
                'total_amount'        => 0,
                'pricing_tier'        => 'RETAIL',
                'processed_by'        => 'admin-001',
                'confirmed_at'        => in_array($status, ['CONFIRMED', 'PROCESSING', 'PACKED', 'SHIPPED', 'DELIVERED']) ? $createdAt->copy()->addHours(rand(1, 6)) : null,
                'shipped_at'          => in_array($status, ['SHIPPED', 'DELIVERED']) ? $createdAt->copy()->addDays(2) : null,
                'delivered_at'        => $status === 'DELIVERED' ? $createdAt->copy()->addDays(5) : null,
                'created_at'          => $createdAt,
            ]);

            $this->seedOrderItems($order, $products);

            if (in_array($status, ['SHIPPED', 'DELIVERED'])) {
                $this->seedFulfillment($order, $createdAt, $status);
            }
        }

        // ─────────────────────────────────────────
        // SECTION 2: DISTRIBUTOR ORDERS
        // ─────────────────────────────────────────
        $distributors = DistributorProfile::whereNotNull('approved_at')->take(6)->get();

        if ($distributors->isEmpty()) {
            $this->command->warn('OrderSeeder: No approved distributors found. Skipping distributor order seeding.');
            return;
        }

        /**
         * Distributor orders — bulk B2B orders with DISTRIBUTOR pricing tier.
         * No customer_id, no shipping address (pickup/delivery to distributor).
         * Distributed across the same Kanban column statuses.
         */
        $distStatusGroups = [
            ['PENDING',    'UNPAID',  [0, 2]],
            ['PENDING',    'PAID',    [1, 3]],
            ['CONFIRMED',  'PAID',    [1, 4]],
            ['CONFIRMED',  'UNPAID',  [2, 5]],
            ['PROCESSING', 'PAID',    [2, 6]],
            ['PROCESSING', 'PAID',    [3, 7]],
            ['PACKED',     'PAID',    [4, 8]],
            ['PACKED',     'PAID',    [5, 9]],
            ['SHIPPED',    'PAID',    [6, 12]],
            ['SHIPPED',    'PAID',    [7, 14]],
            ['DELIVERED',  'PAID',    [10, 20]],
            ['DELIVERED',  'PAID',    [12, 25]],
        ];

        foreach ($distStatusGroups as $i => [$status, $paymentStatus, $daysRange]) {
            $distributor = $distributors[$i % $distributors->count()];
            $city        = $distributor->assigned_city ?? $cities[array_rand($cities)];
            $daysAgo     = rand($daysRange[0], $daysRange[1]);
            $createdAt   = now()->subDays($daysAgo)->subHours(rand(0, 23));
            $payMethod   = $paymentMethods[array_rand($paymentMethods)];

            // Distributors often pick up in bulk, so higher quantities
            $order = Order::create([
                'order_number'        => 'DST-' . strtoupper(Str::random(8)),
                'customer_id'         => null,
                'distributor_id'      => $distributor->id,
                'reseller_id'         => null,
                'source'              => 'DISTRIBUTOR',
                'status'              => $status,
                'payment_status'      => $paymentStatus,
                'payment_method'      => $payMethod,
                'shipping_address_id' => null,
                'subtotal'            => 0,
                'shipping_fee'        => 0,    // Often free for distributors
                'discount_amount'     => 0,
                'total_amount'        => 0,
                'pricing_tier'        => 'DISTRIBUTOR',
                'internal_notes'      => "Bulk order from distributor {$distributor->distributor_code} ({$city})",
                'processed_by'        => 'admin-001',
                'confirmed_at'        => in_array($status, ['CONFIRMED', 'PROCESSING', 'PACKED', 'SHIPPED', 'DELIVERED']) ? $createdAt->copy()->addHours(rand(1, 4)) : null,
                'shipped_at'          => in_array($status, ['SHIPPED', 'DELIVERED']) ? $createdAt->copy()->addDays(1) : null,
                'delivered_at'        => $status === 'DELIVERED' ? $createdAt->copy()->addDays(3) : null,
                'created_at'          => $createdAt,
            ]);

            // Bulk order: 3-8 items per line, higher unit quantities
            $this->seedOrderItems($order, $products, minItems: 2, maxItems: 5, minQty: 3, maxQty: 10, pricingTier: 'DISTRIBUTOR');

            if (in_array($status, ['SHIPPED', 'DELIVERED'])) {
                $this->seedFulfillment($order, $createdAt, $status);
            }
        }
    }

    private function seedOrderItems(
        Order $order,
        $products,
        int $minItems = 1,
        int $maxItems = 3,
        int $minQty = 1,
        int $maxQty = 3,
        string $pricingTier = 'RETAIL'
    ): void {
        $subtotal   = 0;
        $itemsCount = rand($minItems, $maxItems);

        for ($j = 0; $j < $itemsCount; $j++) {
            $product = $products->random();
            $variant = $product->variants->first();

            if (!$variant) {
                continue;
            }

            // Try matching pricing tier, fall back to any pricing
            $pricing = $variant->pricing->where('tier', $pricingTier)->first()
                    ?? $variant->pricing->first();

            $price = $pricing ? (float) $pricing->price : 1200.00;
            $qty   = rand($minQty, $maxQty);

            OrderItem::create([
                'order_id'     => $order->id,
                'product_id'   => $product->id,
                'variant_id'   => $variant->id,
                'product_name' => $product->name,
                'variant_name' => $variant->name,
                'sku'          => $variant->sku,
                'quantity'     => $qty,
                'unit_price'   => $price,
                'total_price'  => $price * $qty,
            ]);

            $subtotal += ($price * $qty);
        }

        $order->update([
            'subtotal'     => $subtotal,
            'total_amount' => $subtotal + (float) $order->shipping_fee,
        ]);
    }

    private function seedFulfillment(Order $order, $createdAt, string $status): void
    {
        $couriers = ['J&T Express', 'LBC Express', 'NinjaVan', '2GO Express', 'Grab Express'];

        Fulfillment::create([
            'order_id'           => $order->id,
            'courier_name'       => $couriers[array_rand($couriers)],
            'tracking_number'    => strtoupper(Str::random(2)) . rand(1000000000, 9999999999),
            'shipped_at'         => $createdAt->copy()->addDays(2),
            'estimated_delivery' => $createdAt->copy()->addDays(5),
            'delivered_at'       => $status === 'DELIVERED' ? $createdAt->copy()->addDays(5) : null,
        ]);
    }
}
