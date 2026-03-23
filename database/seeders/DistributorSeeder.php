<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\DistributorProfile;
use App\Models\DistributorRankHistory;
use App\Models\Wallet;
use Illuminate\Database\Seeder;

final class DistributorSeeder extends Seeder
{
    public function run(): void
    {
        // Gold-tier distributors
        $manilaDist = DistributorProfile::create([
            'user_id'              => 'usr-dist-manila-01',
            'distributor_code'     => 'DIST-GOLD-004',
            'rank'                 => 'GOLD',
            'referral_code'        => 'REFGOLD04',
            'assigned_city'        => 'Manila',
            'approved_at'          => now()->subMonths(8),
            'approved_by'          => 'admin-001',
            'total_network_sales'  => 1120000.00,
            'total_personal_sales' => 480000.00,
            'bank_name'            => 'BDO',
            'bank_account_name'    => 'Rosario Villanueva',
            'bank_account_number'  => '3301234567',
            'e_wallet_gcash'       => '09178881234',
        ]);

        $taguigDist = DistributorProfile::create([
            'user_id'              => 'usr-dist-taguig-01',
            'distributor_code'     => 'DIST-GOLD-005',
            'rank'                 => 'GOLD',
            'referral_code'        => 'REFGOLD05',
            'assigned_city'        => 'Taguig',
            'approved_at'          => now()->subMonths(7),
            'approved_by'          => 'admin-001',
            'total_network_sales'  => 940000.00,
            'total_personal_sales' => 390000.00,
            'bank_name'            => 'BPI',
            'bank_account_name'    => 'Emmanuel Pascual',
            'bank_account_number'  => '4412345678',
            'e_wallet_maya'        => '09292341234',
        ]);

        // Silver-tier distributors
        $pasigDist = DistributorProfile::create([
            'user_id'               => 'usr-dist-pasig-01',
            'distributor_code'      => 'DIST-SILV-006',
            'rank'                  => 'SILVER',
            'referral_code'         => 'REFSILV06',
            'parent_distributor_id' => $manilaDist->id,
            'assigned_city'         => 'Pasig',
            'approved_at'           => now()->subMonths(5),
            'approved_by'           => 'admin-001',
            'total_network_sales'   => 360000.00,
            'total_personal_sales'  => 140000.00,
            'bank_name'             => 'Metrobank',
            'bank_account_name'     => 'Angelica Torres',
            'bank_account_number'   => '5523456789',
            'e_wallet_gcash'        => '09151112233',
        ]);

        $davaoDist = DistributorProfile::create([
            'user_id'               => 'usr-dist-davao-01',
            'distributor_code'      => 'DIST-SILV-007',
            'rank'                  => 'SILVER',
            'referral_code'         => 'REFSILV07',
            'parent_distributor_id' => $taguigDist->id,
            'assigned_city'         => 'Davao City',
            'approved_at'           => now()->subMonths(4),
            'approved_by'           => 'admin-001',
            'total_network_sales'   => 285000.00,
            'total_personal_sales'  => 115000.00,
            'bank_name'             => 'UnionBank',
            'bank_account_name'     => 'Ricardo Fernandez',
            'bank_account_number'   => '6634567890',
            'e_wallet_gcash'        => '09279998877',
        ]);

        $cagayanDist = DistributorProfile::create([
            'user_id'               => 'usr-dist-cdo-01',
            'distributor_code'      => 'DIST-SILV-008',
            'rank'                  => 'SILVER',
            'referral_code'         => 'REFSILV08',
            'parent_distributor_id' => $davaoDist->id,
            'assigned_city'         => 'Cagayan de Oro',
            'approved_at'           => now()->subMonths(3),
            'approved_by'           => 'admin-001',
            'total_network_sales'   => 190000.00,
            'total_personal_sales'  => 85000.00,
            'bank_name'             => 'Landbank',
            'bank_account_name'     => 'Marilou Bautista',
            'bank_account_number'   => '7745678901',
            'e_wallet_maya'         => '09183334455',
        ]);

        // Starter-tier distributors
        $iloiloDist = DistributorProfile::create([
            'user_id'               => 'usr-dist-iloilo-01',
            'distributor_code'      => 'DIST-STRT-009',
            'rank'                  => 'STARTER',
            'referral_code'         => 'REFSTRT09',
            'parent_distributor_id' => $taguigDist->id,
            'assigned_city'         => 'Iloilo City',
            'approved_at'           => now()->subMonths(2),
            'approved_by'           => 'admin-001',
            'total_network_sales'   => 55000.00,
            'total_personal_sales'  => 55000.00,
            'bank_name'             => 'BDO',
            'bank_account_name'     => 'Corazon Espiritu',
            'bank_account_number'   => '8856789012',
            'e_wallet_gcash'        => '09176665544',
        ]);

        $bacolodDist = DistributorProfile::create([
            'user_id'               => 'usr-dist-bacolod-01',
            'distributor_code'      => 'DIST-STRT-010',
            'rank'                  => 'STARTER',
            'referral_code'         => 'REFSTRT10',
            'parent_distributor_id' => $pasigDist->id,
            'assigned_city'         => 'Bacolod',
            'approved_at'           => now()->subWeeks(7),
            'approved_by'           => 'admin-001',
            'total_network_sales'   => 42000.00,
            'total_personal_sales'  => 42000.00,
            'bank_name'             => 'BPI',
            'bank_account_name'     => 'Fernando Lacson',
            'bank_account_number'   => '9967890123',
            'e_wallet_maya'         => '09284443322',
        ]);

        $angelesDist = DistributorProfile::create([
            'user_id'               => 'usr-dist-angeles-01',
            'distributor_code'      => 'DIST-STRT-011',
            'rank'                  => 'STARTER',
            'referral_code'         => 'REFSTRT11',
            'parent_distributor_id' => $manilaDist->id,
            'assigned_city'         => 'Angeles City',
            'approved_at'           => now()->subWeeks(5),
            'approved_by'           => 'admin-001',
            'total_network_sales'   => 29000.00,
            'total_personal_sales'  => 29000.00,
            'bank_name'             => 'Metrobank',
            'bank_account_name'     => 'Natividad Cruz',
            'bank_account_number'   => '1078901234',
            'e_wallet_gcash'        => '09152221100',
        ]);

        $antipoloDist = DistributorProfile::create([
            'user_id'               => 'usr-dist-antipolo-01',
            'distributor_code'      => 'DIST-STRT-012',
            'rank'                  => 'STARTER',
            'referral_code'         => 'REFSTRT12',
            'parent_distributor_id' => $pasigDist->id,
            'assigned_city'         => 'Antipolo',
            'approved_at'           => now()->subWeeks(4),
            'approved_by'           => 'admin-001',
            'total_network_sales'   => 21500.00,
            'total_personal_sales'  => 21500.00,
            'bank_name'             => 'UnionBank',
            'bank_account_name'     => 'Domingo Aquino',
            'bank_account_number'   => '1189012345',
        ]);

        $genSanDist = DistributorProfile::create([
            'user_id'               => 'usr-dist-gensan-01',
            'distributor_code'      => 'DIST-STRT-013',
            'rank'                  => 'STARTER',
            'referral_code'         => 'REFSTRT13',
            'parent_distributor_id' => $davaoDist->id,
            'assigned_city'         => 'General Santos',
            'approved_at'           => now()->subWeeks(3),
            'approved_by'           => 'admin-001',
            'total_network_sales'   => 18000.00,
            'total_personal_sales'  => 18000.00,
            'bank_name'             => 'Landbank',
            'bank_account_name'     => 'Esperanza Gallo',
            'bank_account_number'   => '1290123456',
            'e_wallet_gcash'        => '09271110099',
        ]);

        $baguioDist = DistributorProfile::create([
            'user_id'               => 'usr-dist-baguio-01',
            'distributor_code'      => 'DIST-STRT-014',
            'rank'                  => 'STARTER',
            'referral_code'         => 'REFSTRT14',
            'parent_distributor_id' => $manilaDist->id,
            'assigned_city'         => 'Baguio',
            'approved_at'           => now()->subWeeks(2),
            'approved_by'           => 'admin-001',
            'total_network_sales'   => 14500.00,
            'total_personal_sales'  => 14500.00,
            'bank_name'             => 'BDO',
            'bank_account_name'     => 'Teresita Ramos',
            'bank_account_number'   => '1301234567',
            'e_wallet_maya'         => '09193332211',
        ]);

        // Pending applicant — not yet approved
        DistributorProfile::create([
            'user_id'               => 'usr-dist-zambo-01',
            'distributor_code'      => 'DIST-STRT-015',
            'rank'                  => 'STARTER',
            'referral_code'         => 'REFSTRT15',
            'parent_distributor_id' => $cagayanDist->id,
            'assigned_city'         => 'Zamboanga City',
            'approved_at'           => null,
            'total_network_sales'   => 0.00,
            'total_personal_sales'  => 0.00,
            'application_doc_url'   => 'https://docs.example.com/dist-zambo-01.pdf',
        ]);

        // Rank history entries for promoted distributors
        DistributorRankHistory::create([
            'distributor_id' => $manilaDist->id,
            'previous_rank'  => 'SILVER',
            'new_rank'       => 'GOLD',
            'changed_by'     => 'admin-001',
            'reason'         => 'Exceeded Gold network sales threshold for Q4 2025',
        ]);

        DistributorRankHistory::create([
            'distributor_id' => $taguigDist->id,
            'previous_rank'  => 'SILVER',
            'new_rank'       => 'GOLD',
            'changed_by'     => 'admin-001',
            'reason'         => 'Sustained Gold-level sales for 3 consecutive months',
        ]);

        DistributorRankHistory::create([
            'distributor_id' => $pasigDist->id,
            'previous_rank'  => 'STARTER',
            'new_rank'       => 'SILVER',
            'changed_by'     => 'admin-001',
            'reason'         => 'Reached Silver network sales milestone',
        ]);

        DistributorRankHistory::create([
            'distributor_id' => $davaoDist->id,
            'previous_rank'  => 'STARTER',
            'new_rank'       => 'SILVER',
            'changed_by'     => 'admin-001',
            'reason'         => 'Silver threshold crossed via regional expansion',
        ]);

        DistributorRankHistory::create([
            'distributor_id' => $cagayanDist->id,
            'previous_rank'  => 'STARTER',
            'new_rank'       => 'SILVER',
            'changed_by'     => 'admin-001',
            'reason'         => 'Consistent monthly growth over 90 days',
        ]);

        // Wallets for all approved distributors
        $wallets = [
            [$manilaDist->id,   28500.00,  4200.00, 195000.00, 166500.00],
            [$taguigDist->id,   19800.00,  3100.00, 142000.00, 122200.00],
            [$pasigDist->id,     8400.00,  1200.00,  48000.00,  39600.00],
            [$davaoDist->id,     5600.00,   800.00,  31500.00,  25900.00],
            [$cagayanDist->id,   3200.00,   500.00,  18200.00,  15000.00],
            [$iloiloDist->id,    1800.00,   300.00,   8400.00,   6600.00],
            [$bacolodDist->id,   1200.00,   200.00,   5800.00,   4600.00],
            [$angelesDist->id,    900.00,   150.00,   4100.00,   3200.00],
            [$antipoloDist->id,   600.00,   100.00,   2800.00,   2200.00],
            [$genSanDist->id,     500.00,    80.00,   2300.00,   1800.00],
            [$baguioDist->id,     400.00,    60.00,   1700.00,   1300.00],
        ];

        foreach ($wallets as [$distId, $balance, $pending, $earned, $withdrawn]) {
            Wallet::create([
                'distributor_id'     => $distId,
                'balance'            => $balance,
                'pending_balance'    => $pending,
                'lifetime_earned'    => $earned,
                'lifetime_withdrawn' => $withdrawn,
            ]);
        }
    }
}
