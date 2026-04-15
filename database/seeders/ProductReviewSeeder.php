<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Database\Seeder;

class ProductReviewSeeder extends Seeder
{
    public function run(): void
    {
        // Get existing active products
        $products = Product::where('is_active', true)->get();

        if ($products->isEmpty()) {
            $this->command->warn('No active products found. Skipping review seeding.');
            return;
        }

        // Sample user IDs (user_id is a string cross-db reference to feralde_auth)
        $userIds = [
            'usr_' . str_pad('1', 16, '0', STR_PAD_LEFT),
            'usr_' . str_pad('2', 16, '0', STR_PAD_LEFT),
            'usr_' . str_pad('3', 16, '0', STR_PAD_LEFT),
            'usr_' . str_pad('4', 16, '0', STR_PAD_LEFT),
            'usr_' . str_pad('5', 16, '0', STR_PAD_LEFT),
        ];

        // Get all available product images for random assignment
        $allProductIds = $products->pluck('id')->toArray();

        // Seed reviews for each product
        $totalReviews = 0;
        foreach ($products as $product) {
            // Create 5-8 reviews per product
            $reviewCount = rand(5, 8);

            for ($i = 0; $i < $reviewCount; $i++) {
                // Pick random user ID
                $userId = $userIds[array_rand($userIds)];
                
                // Pick a random product ID for the image (can be different from current product)
                $randomProductId = $allProductIds[array_rand($allProductIds)];

                // Create review
                ProductReview::create([
                    'product_id'  => $product->id,
                    'user_id'     => $userId,
                    'order_id'    => null,
                    'rating'      => rand(1, 5),
                    'title'       => $this->generateNameWithLocation(),
                    'body'        => $this->generateReviewBody(),
                    'is_verified' => rand(0, 100) > 30, // 70% verified
                    'is_approved' => rand(0, 100) > 20, // 80% approved
                ]);
            }

            $totalReviews += $reviewCount;
        }

        $this->command->info("ProductReviewSeeder completed. Seeded {$totalReviews} reviews.");
    }

    private function generateNameWithLocation(): string
    {
        $firstNames = ['Sonia', 'Alan', 'Maria', 'Juan', 'Rosa', 'Miguel', 'Anna', 'Carlos', 'Lucia', 'Diego', 'Carmen', 'Rafael', 'Elena', 'Luis', 'Sofia'];
        $lastInitials = ['D', 'C', 'M', 'T', 'R', 'P', 'L', 'G', 'V', 'S', 'A', 'B', 'F', 'H', 'J'];
        $locations = ['Manila', 'Bicol', 'Cebu', 'Davao', 'Laguna', 'Cavite', 'Pampanga', 'Bulacan', 'Rizal', 'Quezon', 'Camarines', 'Albay', 'Leyte', 'Iloilo', 'Bacolod'];

        $firstName = $firstNames[array_rand($firstNames)];
        $lastInitial = $lastInitials[array_rand($lastInitials)];
        $location = $locations[array_rand($locations)];

        return "{$firstName} {$lastInitial}. - {$location}";
    }

    private function generateReviewBody(): string
    {
        $reviews = [
            'This fragrance has become my go-to scent. The projection is fantastic and it lasts all day. Highly recommended for anyone looking for a quality perfume.',
            'I was initially skeptical, but after trying it, I\'m absolutely hooked. The scent profile is unique and sophisticated. Definitely worth the investment.',
            'Great scent, but the longevity could be better. It fades after a few hours. Still a decent product though.',
            'Outstanding quality! The packaging is beautiful and the scent is even better. I\'ve gotten so many compliments since I started wearing this.',
            'Finally found a fragrance that truly represents what I was looking for. Blends perfectly with my skin chemistry. 10/10 would buy again.',
            'Not bad, but I was expecting more based on the reviews. It\'s decent, but nothing extraordinary in my opinion.',
            'This is my third purchase and I still love it. The consistency is impressive and the scent stays true to what I got the first time.',
            'The scent is divine! It\'s sophisticated, long-lasting, and gets compliments everywhere I go. Worth every centavo!',
            'Good product, good price. No complaints. It does what it promises and smells great.',
            'I\'ve tried many fragrances before, but this one is genuinely special. The notes blend harmoniously and the performance is top-tier.',
        ];

        return $reviews[array_rand($reviews)];
    }
}
