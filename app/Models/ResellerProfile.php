<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

final class ResellerProfile extends Model
{
    protected $fillable = [
        'user_id',
        'reseller_code',
        'referral_code',
        'parent_distributor_id',
        'city',
        'approved_at',
        'approved_by',
        'total_sales',
        'bank_account_name',
        'bank_account_number',
        'bank_name',
        'e_wallet_gcash',
        'e_wallet_maya',
    ];

    protected $casts = [
        'total_sales' => 'decimal:4',
        'approved_at' => 'datetime',
    ];

    public function parentDistributor(): BelongsTo
    {
        return $this->belongsTo(DistributorProfile::class, 'parent_distributor_id');
    }

    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class, 'reseller_id');
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(Commission::class, 'reseller_id');
    }

    /**
     * Resolve the effective distributor for this reseller.
     *
     * Priority:
     * 1. Direct invitation (parent_distributor_id set)
     * 2. City-based assignment (reseller's city has an assigned distributor)
     * 3. null (direct ordering, no distributor covers this reseller)
     */
    public function getEffectiveDistributor(): ?DistributorProfile
    {
        if ($this->parent_distributor_id !== null) {
            return $this->parentDistributor;
        }

        if ($this->city !== null) {
            return DistributorProfile::getForCity($this->city);
        }

        return null;
    }

    /**
     * Whether this reseller can order directly (no distributor in their city
     * and no direct parent distributor).
     */
    public function isDirectOrder(): bool
    {
        return $this->getEffectiveDistributor() === null;
    }

    public static function getAll(array $filters = []): array
    {
        $query = self::with(['parentDistributor', 'wallet']);

        if (!empty($filters['distributor_id'])) {
            $distributorId = (int) $filters['distributor_id'];
            $distributor   = DistributorProfile::find($distributorId);

            // Include directly-invited resellers AND city-based resellers
            $query->where(function (Builder $q) use ($distributorId, $distributor): void {
                $q->where('parent_distributor_id', $distributorId);

                if ($distributor?->assigned_city !== null) {
                    $q->orWhere('city', $distributor->assigned_city);
                }
            });
        }

        if (!empty($filters['city'])) {
            $query->where('city', $filters['city']);
        }

        if (isset($filters['direct_order'])) {
            if ($filters['direct_order']) {
                // No direct distributor AND city has no assigned distributor
                $query->whereNull('parent_distributor_id')
                      ->whereNotIn('city', DistributorProfile::whereNotNull('assigned_city')->pluck('assigned_city'));
            } else {
                $query->where(function (Builder $q): void {
                    $q->whereNotNull('parent_distributor_id')
                      ->orWhereIn('city', DistributorProfile::whereNotNull('assigned_city')->pluck('assigned_city'));
                });
            }
        }

        if (!empty($filters['search'])) {
            $query->where(function (Builder $q) use ($filters): void {
                $q->where('reseller_code', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('city', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('user_id', $filters['search']);
            });
        }

        $perPage   = (int) ($filters['per_page'] ?? 20);
        $resellers = $query->orderByDesc('created_at')->paginate($perPage);

        return [
            'resellers' => $resellers->items(),
            'pagination' => [
                'current_page' => $resellers->currentPage(),
                'last_page'    => $resellers->lastPage(),
                'per_page'     => $resellers->perPage(),
                'total'        => $resellers->total(),
            ],
        ];
    }

    public function approve(string $approvedBy): bool
    {
        return DB::transaction(function () use ($approvedBy): bool {
            $result = $this->update([
                'approved_at' => now(),
                'approved_by' => $approvedBy,
            ]);

            Wallet::firstOrCreate(['reseller_id' => $this->id]);

            return $result;
        });
    }
}
