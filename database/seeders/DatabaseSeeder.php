<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AccountingPeriod;
use App\Models\Announcement;
use App\Models\Bundle;
use App\Models\BundleItem;
use App\Models\ChannelProduct;
use App\Models\CommissionRule;
use App\Models\DistributorProfile;
use App\Models\DistributorRankHistory;
use App\Models\Expense;
use App\Models\LeaderboardEntry;
use App\Models\LedgerEntry;
use App\Models\MarketingAsset;
use App\Models\ProductVariant;
use App\Models\PromoCode;
use App\Models\ResellerProfile;
use App\Models\SalesChannel;
use App\Models\SystemSetting;
use App\Models\TrainingCompletion;
use App\Models\TrainingContent;
use App\Models\TrainingModule;
use App\Models\Wallet;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // -------------------------
        // Warehouse
        // -------------------------
        $warehouse = Warehouse::updateOrCreate(
            ['code' => 'WH-MAIN'],
            [
                'name'       => 'Main Warehouse',
                'address'    => 'Binondo, Manila, Philippines',
                'is_default' => true,
            ]
        );

        // -------------------------
        // Categories + Products + Reviews
        // -------------------------
        $this->call([
            CategorySeeder::class,
            ProductSeeder::class,
            ProductReviewSeeder::class,
        ]);

        // -------------------------
        // Bundle (uses first 3 real product variants)
        // -------------------------
        $allVariants = ProductVariant::with('product')->take(9)->get()
            ->map(fn($v) => ['variant' => $v, 'product' => $v->product])
            ->toArray();

        $bundle = Bundle::create([
            'name'              => 'Signature Trio Set',
            'slug'              => 'signature-trio-set',
            'description'       => 'Three iconic fragrances at a special bundle price.',
            'type'              => 'GIFT_SET',
            'retail_price'      => 24999,
            'distributor_price' => 18749,
            'is_active'         => true,
        ]);

        // Bundle items: 50ml variant of first 3 products
        foreach (array_slice($allVariants, 1, 3) as $item) {
            BundleItem::create([
                'bundle_id'  => $bundle->id,
                'product_id' => $item['product']->id,
                'variant_id' => $item['variant']->id,
                'quantity'   => 1,
            ]);
        }

        // -------------------------
        // Promo Codes
        // -------------------------
        PromoCode::create([
            'code'             => 'WELCOME20',
            'type'             => 'PERCENTAGE_DISCOUNT',
            'value'            => 20,
            'min_order_amount' => 1000,
            'usage_limit'      => 500,
            'usage_count'      => 12,
            'is_active'        => true,
            'starts_at'        => now()->subMonth(),
            'ends_at'          => now()->addMonths(6),
        ]);

        PromoCode::create([
            'code'             => 'FLAT500',
            'type'             => 'FIXED_DISCOUNT',
            'value'            => 500,
            'min_order_amount' => 5000,
            'usage_limit'      => 200,
            'usage_count'      => 3,
            'is_active'        => true,
            'starts_at'        => now()->subWeek(),
            'ends_at'          => now()->addMonths(3),
        ]);

        PromoCode::create([
            'code'      => 'FREESHIP',
            'type'      => 'FREE_SHIPPING',
            'value'     => 0,
            'is_active' => true,
            'starts_at' => now()->subDays(3),
            'ends_at'   => now()->addMonth(),
        ]);

        // -------------------------
        // Distributor Profiles
        // -------------------------
        $goldDist = DistributorProfile::create([
            'user_id'              => 'usr-gold-001',
            'distributor_code'     => 'DIST-GOLD-001',
            'rank'                 => 'GOLD',
            'referral_code'        => 'REFGOLD01',
            'assigned_city'        => 'Makati',
            'approved_at'          => now()->subMonths(6),
            'approved_by'          => 'admin-001',
            'total_network_sales'  => 850000.00,
            'total_personal_sales' => 320000.00,
            'bank_name'            => 'BDO',
            'bank_account_name'    => 'Maria Santos',
            'bank_account_number'  => '1234567890',
            'e_wallet_gcash'       => '09171234567',
        ]);

        $silverDist = DistributorProfile::create([
            'user_id'               => 'usr-silver-002',
            'distributor_code'      => 'DIST-SILV-002',
            'rank'                  => 'SILVER',
            'referral_code'         => 'REFSILV02',
            'parent_distributor_id' => $goldDist->id,
            'assigned_city'         => 'Quezon City',
            'approved_at'           => now()->subMonths(3),
            'approved_by'           => 'admin-001',
            'total_network_sales'   => 210000.00,
            'total_personal_sales'  => 95000.00,
            'bank_name'             => 'Metrobank',
            'bank_account_name'     => 'Juan dela Cruz',
            'bank_account_number'   => '9876543210',
            'e_wallet_maya'         => '09281234567',
        ]);

        DistributorProfile::create([
            'user_id'               => 'usr-starter-003',
            'distributor_code'      => 'DIST-STRT-003',
            'rank'                  => 'STARTER',
            'referral_code'         => 'REFSTRT03',
            'parent_distributor_id' => $silverDist->id,
            'assigned_city'         => 'Cebu City',
            'approved_at'           => now()->subMonth(),
            'approved_by'           => 'admin-001',
            'total_network_sales'   => 32000.00,
            'total_personal_sales'  => 32000.00,
        ]);

        DistributorRankHistory::create([
            'distributor_id' => $goldDist->id,
            'previous_rank'  => 'SILVER',
            'new_rank'       => 'GOLD',
            'changed_by'     => 'admin-001',
            'reason'         => 'Reached Gold network sales threshold',
        ]);

        // -------------------------
        // Wallets
        // -------------------------
        Wallet::create([
            'distributor_id'    => $goldDist->id,
            'balance'           => 12500.00,
            'pending_balance'   => 2100.00,
            'lifetime_earned'   => 87200.00,
            'lifetime_withdrawn'=> 74700.00,
        ]);

        Wallet::create([
            'distributor_id'    => $silverDist->id,
            'balance'           => 4200.00,
            'pending_balance'   => 800.00,
            'lifetime_earned'   => 18600.00,
            'lifetime_withdrawn'=> 14400.00,
        ]);

        // -------------------------
        // Reseller Profiles
        // -------------------------
        // Reseller in Makati (gold dist city) — directly invited by goldDist
        ResellerProfile::create([
            'user_id'               => 'usr-res-001',
            'reseller_code'         => 'RES-001-A',
            'referral_code'         => 'RESREF001',
            'parent_distributor_id' => $goldDist->id,
            'city'                  => 'Makati',
            'approved_at'           => now()->subMonths(2),
            'approved_by'           => 'admin-001',
            'total_sales'           => 45000.00,
            'bank_name'             => 'BPI',
            'bank_account_name'     => 'Ana Reyes',
            'bank_account_number'   => '1122334455',
            'e_wallet_gcash'        => '09151234567',
        ]);

        // Reseller in Quezon City (silver dist city) — city-based, no direct invite
        ResellerProfile::create([
            'user_id'               => 'usr-res-002',
            'reseller_code'         => 'RES-002-B',
            'referral_code'         => 'RESREF002',
            'parent_distributor_id' => null,
            'city'                  => 'Quezon City',
            'approved_at'           => now()->subMonth(),
            'approved_by'           => 'admin-001',
            'total_sales'           => 18000.00,
        ]);

        // Reseller in Davao (no distributor) — direct ordering
        ResellerProfile::create([
            'user_id'               => 'usr-res-003',
            'reseller_code'         => 'RES-003-C',
            'referral_code'         => 'RESREF003',
            'parent_distributor_id' => null,
            'city'                  => 'Davao City',
            'approved_at'           => now()->subWeeks(2),
            'approved_by'           => 'admin-001',
            'total_sales'           => 9500.00,
        ]);

        // -------------------------
        // Commission Rules
        // -------------------------
        CommissionRule::create([
            'name'                  => 'Direct Sale Commission',
            'commission_type'       => 'PERSONAL_SALE',
            'personal_sale_rate'    => 15.00,
            'is_active'             => true,
            'effective_from'        => now()->subYear(),
        ]);

        CommissionRule::create([
            'name'                   => 'Reseller Override Commission',
            'commission_type'        => 'RESELLER_OVERRIDE',
            'applicable_rank'        => 'GOLD',
            'reseller_override_rate' => 5.00,
            'is_active'              => true,
            'effective_from'         => now()->subYear(),
        ]);

        // Note: Commission records require order_id (FK to orders). Skipped since no orders are seeded.

        // -------------------------
        // Accounting Periods
        // -------------------------
        $prevPeriod = AccountingPeriod::create([
            'year'      => 2026,
            'month'     => 2,
            'is_closed' => true,
            'closed_at' => now()->subDays(7),
        ]);

        $currPeriod = AccountingPeriod::create([
            'year'      => 2026,
            'month'     => 3,
            'is_closed' => false,
        ]);

        // -------------------------
        // Ledger Entries
        // -------------------------
        LedgerEntry::create([
            'period_id'   => $prevPeriod->id,
            'entry_date'  => '2026-02-28 23:59:00',
            'entry_type'  => 'REVENUE',
            'category'    => null,
            'description' => 'Direct retail sales — February 2026',
            'amount'      => 620000.00,
            'credit'      => 620000.00,
            'debit'       => 0,
        ]);

        LedgerEntry::create([
            'period_id'   => $prevPeriod->id,
            'entry_date'  => '2026-02-28 23:59:00',
            'entry_type'  => 'REVENUE',
            'category'    => null,
            'description' => 'Distributor channel sales — February 2026',
            'amount'      => 280000.00,
            'credit'      => 280000.00,
            'debit'       => 0,
        ]);

        LedgerEntry::create([
            'period_id'   => $prevPeriod->id,
            'entry_date'  => '2026-02-28 23:59:00',
            'entry_type'  => 'EXPENSE',
            'category'    => 'COMMISSION_PAYOUT',
            'description' => 'Distributor commission payouts — February 2026',
            'amount'      => 87200.00,
            'debit'       => 87200.00,
            'credit'      => 0,
        ]);

        LedgerEntry::create([
            'period_id'   => $currPeriod->id,
            'entry_date'  => '2026-03-09 00:00:00',
            'entry_type'  => 'REVENUE',
            'category'    => null,
            'description' => 'Direct retail sales — March 2026 (partial)',
            'amount'      => 190000.00,
            'credit'      => 190000.00,
            'debit'       => 0,
        ]);

        // -------------------------
        // Expenses
        // -------------------------
        Expense::create([
            'category'     => 'SHIPPING',
            'description'  => 'Shipping and fulfillment — February 2026',
            'amount'       => 42000.00,
            'expense_date' => '2026-02-28 23:59:00',
        ]);

        Expense::create([
            'category'     => 'MARKETING',
            'description'  => 'Social media ads — February 2026',
            'amount'       => 28000.00,
            'expense_date' => '2026-02-28 23:59:00',
        ]);

        Expense::create([
            'category'     => 'SHIPPING',
            'description'  => 'Shipping and fulfillment — March 2026 (partial)',
            'amount'       => 14000.00,
            'expense_date' => '2026-03-09 00:00:00',
        ]);

        // -------------------------
        // Training Modules
        // -------------------------
        $onboarding = TrainingModule::create([
            'title'       => 'Distributor Onboarding',
            'description' => 'Everything you need to know to start your journey as an authorized distributor.',
            'sort_order'  => 1,
            'is_published'=> true,
        ]);

        $productKnowledge = TrainingModule::create([
            'title'       => 'Product Knowledge',
            'description' => 'In-depth guide to our fragrance collection, ingredients, and key selling points.',
            'sort_order'  => 2,
            'is_published'=> true,
        ]);

        $salesSkills = TrainingModule::create([
            'title'       => 'Sales Techniques',
            'description' => 'Proven strategies for growing your personal and network sales.',
            'sort_order'  => 3,
            'is_published'=> true,
        ]);

        // Training Contents
        TrainingContent::create([
            'module_id'   => $onboarding->id,
            'title'       => 'Welcome to Feralde',
            'type'        => 'VIDEO',
            'content_url' => 'https://cdn.example.com/training/welcome.mp4',
            'sort_order'  => 1,
            'is_required' => true,
        ]);

        TrainingContent::create([
            'module_id'   => $onboarding->id,
            'title'       => 'Code of Conduct',
            'type'        => 'PDF',
            'content_url' => 'https://cdn.example.com/training/code-of-conduct.pdf',
            'sort_order'  => 2,
            'is_required' => true,
        ]);

        TrainingContent::create([
            'module_id'   => $productKnowledge->id,
            'title'       => 'Fragrance Families Explained',
            'type'        => 'VIDEO',
            'content_url' => 'https://cdn.example.com/training/fragrance-families.mp4',
            'sort_order'  => 1,
            'is_required' => true,
        ]);

        TrainingContent::create([
            'module_id'   => $productKnowledge->id,
            'title'       => 'Product Catalog Walkthrough',
            'type'        => 'PDF',
            'content_url' => 'https://cdn.example.com/training/catalog.pdf',
            'sort_order'  => 2,
            'is_required' => false,
        ]);

        TrainingContent::create([
            'module_id'   => $salesSkills->id,
            'title'       => 'How to Close a Fragrance Sale',
            'type'        => 'VIDEO',
            'content_url' => 'https://cdn.example.com/training/sales-close.mp4',
            'sort_order'  => 1,
            'is_required' => true,
        ]);

        // Training Completions
        TrainingCompletion::create([
            'module_id'    => $onboarding->id,
            'user_id'      => 'usr-gold-001',
            'score'        => 95,
            'certified'    => true,
            'completed_at' => now()->subMonths(5),
        ]);

        TrainingCompletion::create([
            'module_id'    => $productKnowledge->id,
            'user_id'      => 'usr-gold-001',
            'score'        => 88,
            'certified'    => true,
            'completed_at' => now()->subMonths(4),
        ]);

        TrainingCompletion::create([
            'module_id'    => $onboarding->id,
            'user_id'      => 'usr-silver-002',
            'score'        => 82,
            'certified'    => true,
            'completed_at' => now()->subMonths(2),
        ]);

        // -------------------------
        // Marketing Assets
        // -------------------------
        MarketingAsset::create([
            'title'     => 'Noir Absolu — Product Banner',
            'type'      => 'IMAGE',
            'url'       => 'https://cdn.example.com/marketing/noir-absolu-banner.jpg',
            'is_active' => true,
        ]);

        MarketingAsset::create([
            'title'     => 'Feralde Brand Story Video',
            'type'      => 'VIDEO',
            'url'       => 'https://cdn.example.com/marketing/brand-story.mp4',
            'is_active' => true,
        ]);

        MarketingAsset::create([
            'title'     => 'Distributor Social Media Kit',
            'type'      => 'PDF',
            'url'       => 'https://cdn.example.com/marketing/social-media-kit.pdf',
            'is_active' => true,
        ]);

        // -------------------------
        // Announcements
        // -------------------------
        Announcement::create([
            'title'        => 'Welcome to the New Admin Panel',
            'body'         => 'The new admin panel is now live. Explore distributor management, accounting, training, and more.',
            'is_published' => true,
            'published_at' => now()->subWeek(),
        ]);

        Announcement::create([
            'title'        => 'March Promotions Active',
            'body'         => 'WELCOME20 and FLAT500 promo codes are now active for March. Please communicate these to your networks.',
            'is_published' => true,
            'published_at' => now()->subDays(3),
        ]);

        Announcement::create([
            'title'        => 'New Training Module: Sales Techniques',
            'body'         => 'A new training module on Sales Techniques is now available. All distributors are encouraged to complete it.',
            'is_published' => true,
            'published_at' => now()->subDay(),
        ]);

        // -------------------------
        // System Settings
        // -------------------------
        $settings = [
            ['key' => 'app.name',                     'value' => 'Feralde',      'group' => 'general'],
            ['key' => 'app.timezone',                  'value' => 'Asia/Manila',  'group' => 'general'],
            ['key' => 'app.currency',                  'value' => 'PHP',          'group' => 'general'],
            ['key' => 'app.currency_symbol',           'value' => 'P',            'group' => 'general'],
            ['key' => 'order.min_amount',              'value' => 500,            'group' => 'orders'],
            ['key' => 'shipping.free_threshold',       'value' => 5000,           'group' => 'shipping'],
            ['key' => 'commission.auto_release_days',  'value' => 15,             'group' => 'commissions'],
            ['key' => 'distributor.approval_required', 'value' => true,           'group' => 'distributors'],
            ['key' => 'reseller.approval_required',    'value' => true,           'group' => 'distributors'],
            ['key' => 'leaderboard.period',            'value' => 'monthly',      'group' => 'leaderboard'],
            ['key' => 'maintenance.mode',              'value' => false,          'group' => 'system'],
        ];

        foreach ($settings as $s) {
            SystemSetting::create([
                'key'   => $s['key'],
                'value' => $s['value'],
                'group' => $s['group'],
            ]);
        }

        // -------------------------
        // Sales Channels
        // -------------------------
        SalesChannel::create([
            'name'      => 'Official Website',
            'type'      => 'WEBSITE',
            'is_active' => true,
        ]);

        $tiktok = SalesChannel::create([
            'name'           => 'TikTok Shop PH',
            'type'           => 'TIKTOK_SHOP',
            'is_active'      => true,
            'sync_settings'  => [
                'app_key'    => 'tiktok_app_key_placeholder',
                'shop_id'    => 'tiktok_shop_id_placeholder',
            ],
            'credentials'    => [
                'app_secret' => 'tiktok_app_secret_placeholder',
            ],
            'webhook_secret' => 'tiktok_webhook_secret_placeholder',
        ]);

        $lazada = SalesChannel::create([
            'name'           => 'Lazada PH',
            'type'           => 'LAZADA',
            'is_active'      => true,
            'sync_settings'  => [
                'app_key' => 'lazada_app_key_placeholder',
            ],
            'credentials'    => [
                'app_secret'   => 'lazada_app_secret_placeholder',
                'access_token' => 'lazada_access_token_placeholder',
            ],
            'webhook_secret' => 'lazada_webhook_secret_placeholder',
        ]);

        // Channel product mappings (50ml variant of first 3 products)
        $channelVariants = array_slice($allVariants, 1, 3);
        foreach ($channelVariants as $i => $item) {
            $pad = str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT);

            ChannelProduct::create([
                'channel_id'          => $tiktok->id,
                'variant_id'          => $item['variant']->id,
                'external_listing_id' => 'TT-PROD-' . $pad,
                'external_sku'        => 'TT-SKU-' . ($i + 1),
                'external_price'      => 8499,
                'external_stock'      => 20,
                'is_active'           => true,
                'last_synced_at'      => now()->subHour(),
            ]);

            ChannelProduct::create([
                'channel_id'          => $lazada->id,
                'variant_id'          => $item['variant']->id,
                'external_listing_id' => 'LZ-PROD-' . $pad,
                'external_sku'        => 'LZ-SKU-' . ($i + 1),
                'external_price'      => 8999,
                'external_stock'      => 15,
                'is_active'           => true,
                'last_synced_at'      => now()->subHour(),
            ]);
        }

        // -------------------------
        // Leaderboard Entries
        // -------------------------
        LeaderboardEntry::create([
            'period'         => '2026-03',
            'distributor_id' => $goldDist->id,
            'total_sales'    => 190000.00,
            'total_orders'   => 48,
            'rank'           => 1,
            'badge'          => 'TOP_PERFORMER',
        ]);

        LeaderboardEntry::create([
            'period'         => '2026-03',
            'distributor_id' => $silverDist->id,
            'total_sales'    => 72000.00,
            'total_orders'   => 21,
            'rank'           => 2,
            'badge'          => null,
        ]);

        // Previous month (closed period)
        LeaderboardEntry::create([
            'period'         => '2026-02',
            'distributor_id' => $goldDist->id,
            'total_sales'    => 320000.00,
            'total_orders'   => 89,
            'rank'           => 1,
            'badge'          => 'TOP_PERFORMER',
        ]);

        // -------------------------
        // Additional Distributors & Resellers
        // -------------------------
        $this->call([
            DistributorSeeder::class,
            ResellerSeeder::class,
        ]);

        // -------------------------
        // Sample Orders
        // -------------------------
        $this->call([
            OrderSeeder::class,
        ]);
    }
}
