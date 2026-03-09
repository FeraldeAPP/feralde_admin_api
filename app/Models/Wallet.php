<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

final class Wallet extends Model
{
    protected $fillable = [
        'distributor_id',
        'reseller_id',
        'balance',
        'pending_balance',
        'lifetime_earned',
        'lifetime_withdrawn',
    ];

    protected $casts = [
        'balance'            => 'decimal:4',
        'pending_balance'    => 'decimal:4',
        'lifetime_earned'    => 'decimal:4',
        'lifetime_withdrawn' => 'decimal:4',
    ];

    public function distributor(): BelongsTo
    {
        return $this->belongsTo(DistributorProfile::class, 'distributor_id');
    }

    public function reseller(): BelongsTo
    {
        return $this->belongsTo(ResellerProfile::class, 'reseller_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class);
    }

    public function withdrawals(): HasMany
    {
        return $this->hasMany(WithdrawalRequest::class);
    }

    public function credit(float $amount, string $type, string $description = '', ?string $referenceType = null, ?string $referenceId = null): WalletTransaction
    {
        return DB::transaction(function () use ($amount, $type, $description, $referenceType, $referenceId): WalletTransaction {
            $balanceBefore = (float) $this->balance;
            $balanceAfter  = $balanceBefore + $amount;

            $this->update([
                'balance'         => $balanceAfter,
                'lifetime_earned' => (float) $this->lifetime_earned + $amount,
            ]);

            return $this->transactions()->create([
                'type'           => $type,
                'amount'         => $amount,
                'balance_before' => $balanceBefore,
                'balance_after'  => $balanceAfter,
                'reference_type' => $referenceType,
                'reference_id'   => $referenceId,
                'description'    => $description,
            ]);
        });
    }

    public static function getAll(array $filters = []): array
    {
        $query = self::with(['distributor', 'reseller']);

        $perPage = (int) ($filters['per_page'] ?? 20);
        $wallets = $query->orderByDesc('id')->paginate($perPage);

        return [
            'wallets' => $wallets->items(),
            'pagination' => [
                'current_page' => $wallets->currentPage(),
                'last_page'    => $wallets->lastPage(),
                'per_page'     => $wallets->perPage(),
                'total'        => $wallets->total(),
            ],
        ];
    }
}
