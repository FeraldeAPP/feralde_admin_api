<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

final class Commission extends Model
{
    protected $fillable = [
        'distributor_id',
        'reseller_id',
        'order_id',
        'order_item_id',
        'commission_rule_id',
        'commission_type',
        'base_amount',
        'rate',
        'amount',
        'status',
        'approved_at',
        'approved_by',
        'paid_at',
        'notes',
    ];

    protected $casts = [
        'base_amount' => 'decimal:4',
        'rate'        => 'decimal:4',
        'amount'      => 'decimal:4',
        'approved_at' => 'datetime',
        'paid_at'     => 'datetime',
    ];

    public function distributor(): BelongsTo
    {
        return $this->belongsTo(DistributorProfile::class, 'distributor_id');
    }

    public function reseller(): BelongsTo
    {
        return $this->belongsTo(ResellerProfile::class, 'reseller_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function commissionRule(): BelongsTo
    {
        return $this->belongsTo(CommissionRule::class, 'commission_rule_id');
    }

    public static function getAll(array $filters = []): array
    {
        $query = self::with(['distributor', 'reseller', 'order', 'commissionRule']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['distributor_id'])) {
            $query->where('distributor_id', $filters['distributor_id']);
        }

        if (!empty($filters['reseller_id'])) {
            $query->where('reseller_id', $filters['reseller_id']);
        }

        $perPage     = (int) ($filters['per_page'] ?? 20);
        $commissions = $query->orderByDesc('created_at')->paginate($perPage);

        return [
            'commissions' => $commissions->items(),
            'pagination'  => [
                'current_page' => $commissions->currentPage(),
                'last_page'    => $commissions->lastPage(),
                'per_page'     => $commissions->perPage(),
                'total'        => $commissions->total(),
            ],
        ];
    }

    public function approve(string $approvedBy): bool
    {
        if ($this->status !== 'PENDING') {
            return false;
        }

        return DB::transaction(function () use ($approvedBy): bool {
            return $this->update([
                'status'      => 'APPROVED',
                'approved_at' => now(),
                'approved_by' => $approvedBy,
            ]);
        });
    }

    public function pay(): bool
    {
        if ($this->status !== 'APPROVED') {
            return false;
        }

        return DB::transaction(function (): bool {
            $this->update(['status' => 'PAID', 'paid_at' => now()]);

            $wallet = $this->distributor_id
                ? Wallet::where('distributor_id', $this->distributor_id)->first()
                : Wallet::where('reseller_id', $this->reseller_id)->first();

            if ($wallet) {
                $wallet->credit(
                    (float) $this->amount,
                    'CREDIT_COMMISSION',
                    "Commission payment for order #{$this->order_id}",
                    'commission',
                    (string) $this->id
                );
            }

            return true;
        });
    }
}
