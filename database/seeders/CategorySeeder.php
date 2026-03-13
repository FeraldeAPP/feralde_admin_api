<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        // 1. Perfumes (Root)
        $perfumes = Category::updateOrCreate(
            ['slug' => 'perfumes'],
            [
                'name'        => 'Perfumes',
                'description' => 'Luxury fragrances crafted for every occasion.',
                'is_active'   => true,
                'sort_order'  => 1,
            ]
        );

        // 2. Men's Collection (Child of Perfumes)
        $mens = Category::updateOrCreate(
            ['slug' => 'mens-collection'],
            [
                'name'        => "Men's Collection",
                'parent_id'   => $perfumes->id,
                'description' => 'Bold and distinguished scents for men.',
                'is_active'   => true,
                'sort_order'  => 1,
            ]
        );

        $menScents = ["Fresh & Aquatic", "Woody & Spicy", "Sweet & Gourmand", "Strong & Intense"];
        foreach ($menScents as $index => $name) {
            Category::updateOrCreate(
                ['slug' => Str::slug("men-" . $name)],
                [
                    'name'       => $name,
                    'parent_id'  => $mens->id,
                    'is_active'  => true,
                    'sort_order' => $index,
                ]
            );
        }

        // 3. Women's Collection (Child of Perfumes)
        $womens = Category::updateOrCreate(
            ['slug' => 'womens-collection'],
            [
                'name'        => "Women's Collection",
                'parent_id'   => $perfumes->id,
                'description' => 'Elegant and captivating scents for women.',
                'is_active'   => true,
                'sort_order'  => 2,
            ]
        );

        $womenScents = ["Floral & Powdery", "Sweet & Vanilla", "Fruity & Fresh", "Sexy & Night Out"];
        foreach ($womenScents as $index => $name) {
            Category::updateOrCreate(
                ['slug' => Str::slug("women-" . $name)],
                [
                    'name'       => $name,
                    'parent_id'  => $womens->id,
                    'is_active'  => true,
                    'sort_order' => $index,
                ]
            );
        }

        // 4. Day & Night Picks (Occasions) - Root
        $occasionsRoot = Category::updateOrCreate(
            ['slug' => 'day-night-picks'],
            [
                'name'        => 'Day & Night Picks',
                'description' => 'Fragrances for every moment of your day.',
                'is_active'   => true,
                'sort_order'  => 2,
            ]
        );

        $occasions = ["Office / School Safe", "Everyday Casual", "Date Night", "Party / Clubbing", "Formal Events"];
        foreach ($occasions as $index => $name) {
            Category::updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name'       => $name,
                    'parent_id'  => $occasionsRoot->id,
                    'is_active'  => true,
                    'sort_order' => $index,
                ]
            );
        }

        // 5. Bundle Deals - Root
        $bundlesRoot = Category::updateOrCreate(
            ['slug' => 'bundle-deals'],
            [
                'name'        => 'Bundle Deals',
                'description' => 'Exclusive sets and collections.',
                'is_active'   => true,
                'sort_order'  => 3,
            ]
        );

        $bundleDeals = ["His & Hers Set", "Top 3 Best Sellers", "Starter Discovery Set"];
        foreach ($bundleDeals as $index => $name) {
            Category::updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name'       => $name,
                    'parent_id'  => $bundlesRoot->id,
                    'is_active'  => true,
                    'sort_order' => $index,
                ]
            );
        }
    }
}
