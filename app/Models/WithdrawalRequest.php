<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

final class WithdrawalRequest extends Model
{
    protected $fillable = [
        'wallet_id',
        'distributor_id',
        'reseller_id',
        'amount',
        'fee',
        'net_amount',
        'status',
        'bank_account_name',
        'bank_account_number',
        'bank_name',
        'e_wallet_type',
        'e_wallet_number',
        'reference_number',
        'processed_by',
        'processed_at',
        'rejection_reason',
        'notes',
    ];

    protected $casts = [
        'amount'       => 'decimal:4',
        'fee'          => 'decimal:4',
        'net_amount'   => 'decimal:4',
        'processed_at' => 'datetime',
    ];

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function distributor(): BelongsTo
    {
        return $this->belongsTo(DistributorProfile::class, 'distributor_id');
    }

    public function reseller(): BelongsTo
    {
        return $this->belongsTo(ResellerProfile::class, 'reseller_id');
    }

    public static function getAll(array $filters = []): array
    {
        $query = self::with(['wallet', 'distributor', 'reseller']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $perPage     = (int) ($filters['per_page'] ?? 20);
        $withdrawals = $query->orderByDesc('created_at')->paginate($perPage);

        return [
            'withdrawals' => $withdrawals->items(),
            'pagination'  => [
                'current_page' => $withdrawals->currentPage(),
                'last_page'    => $withdrawals->lastPage(),
                'per_page'     => $withdrawals->perPage(),
                'total'        => $withdrawals->total(),
            ],
        ];
    }

    public function approve(string $processedBy): bool
    {
        return DB::transaction(function () use ($processedBy): bool {
            $result = $this->update([
                'status'       => 'APPROVED',
                'processed_by' => $processedBy,
                'processed_at' => now(),
            ]);

            $this->wallet->update([
                'pending_balance'    => max(0, (float) $this->wallet->pending_balance - (float) $this->amount),
                'lifetime_withdrawn' => (float) $this->wallet->lifetime_withdrawn + (float) $this->net_amount,
            ]);

            return $result;
        });
    }

    public function reject(string $processedBy, string $reason): bool
    {
        return DB::transaction(function () use ($processedBy, $reason): bool {
            $result = $this->update([
                'status'           => 'REJECTED',
                'processed_by'     => $processedBy,
                'processed_at'     => now(),
                'rejection_reason' => $reason,
            ]);

            // Restore balance
            $this->wallet->update([
                'balance'         => (float) $this->wallet->balance + (float) $this->amount,
                'pending_balance' => max(0, (float) $this->wallet->pending_balance - (float) $this->amount),
            ]);

            return $result;
        });
    }
}
