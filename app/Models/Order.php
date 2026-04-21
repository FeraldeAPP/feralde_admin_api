<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

final class Order extends Model
{
    protected $fillable = [
        'order_number',
        'customer_id',
        'guest_email',
        'distributor_id',
        'reseller_id',
        'channel_id',
        'external_order_id',
        'source',
        'status',
        'payment_status',
        'payment_method',
        'shipping_address_id',
        'billing_address_id',
        'subtotal',
        'shipping_fee',
        'discount_amount',
        'tax_amount',
        'total_amount',
        'pricing_tier',
        'customer_notes',
        'internal_notes',
        'processed_by',
        'confirmed_at',
        'shipped_at',
        'delivered_at',
        'cancelled_at',
        'cancellation_reason',
        'payment_proof_url',
    ];

    protected $casts = [
        'subtotal'        => 'decimal:4',
        'shipping_fee'    => 'decimal:4',
        'discount_amount' => 'decimal:4',
        'tax_amount'      => 'decimal:4',
        'total_amount'    => 'decimal:4',
        'confirmed_at'    => 'datetime',
        'shipped_at'      => 'datetime',
        'delivered_at'    => 'datetime',
        'cancelled_at'    => 'datetime',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function fulfillment(): HasOne
    {
        return $this->hasOne(Fulfillment::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(OrderReturn::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(Commission::class);
    }

    public function shippingAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'shipping_address_id');
    }

    public function billingAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'billing_address_id');
    }

    public function distributor(): BelongsTo
    {
        return $this->belongsTo(DistributorProfile::class, 'distributor_id');
    }

    public function reseller(): BelongsTo
    {
        return $this->belongsTo(ResellerProfile::class, 'reseller_id');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(OrderHistory::class)->orderByDesc('created_at');
    }

    public function logHistory(string $action, string $description, ?string $userId = null, ?string $userName = null, array $metadata = []): OrderHistory
    {
        return $this->histories()->create([
            'user_id'     => $userId,
            'user_name'   => $userName,
            'action'      => $action,
            'description' => $description,
            'metadata'    => $metadata,
        ]);
    }

    public static function getAll(array $filters = []): array
    {
        $query = self::with(['items', 'shippingAddress', 'distributor.rankHistory', 'reseller']);

        // Filter by order type: 'shop' = no distributor, 'distributor' = has distributor
        if (!empty($filters['order_type'])) {
            if ($filters['order_type'] === 'shop') {
                $query->whereNull('distributor_id');
            } elseif ($filters['order_type'] === 'distributor') {
                $query->whereNotNull('distributor_id');
            }
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['distributor_id'])) {
            $query->where('distributor_id', $filters['distributor_id']);
        }

        if (!empty($filters['payment_status'])) {
            $query->where('payment_status', $filters['payment_status']);
        }

        if (!empty($filters['source'])) {
            $query->where('source', $filters['source']);
        }

        if (!empty($filters['search'])) {
            $query->where(function (Builder $q) use ($filters): void {
                $q->where('order_number', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('customer_id', $filters['search'])
                  ->orWhereHas('distributor', function (Builder $dq) use ($filters): void {
                      $dq->where('distributor_code', 'like', '%' . $filters['search'] . '%');
                  });
            });
        }

        if (!empty($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        $perPage = (int) ($filters['per_page'] ?? 20);
        $orders  = $query->orderByDesc('created_at')->paginate($perPage);

        return [
            'orders' => $orders->items(),
            'pagination' => [
                'current_page' => $orders->currentPage(),
                'last_page'    => $orders->lastPage(),
                'per_page'     => $orders->perPage(),
                'total'        => $orders->total(),
            ],
        ];
    }

    public function transitionStatus(string $newStatus, string $processedBy): bool
    {
        $allowedTransitions = [
            'PENDING'    => ['CONFIRMED', 'CANCELLED'],
            'CONFIRMED'  => ['PROCESSING', 'CANCELLED'],
            'PROCESSING' => ['PACKED', 'CANCELLED'],
            'PACKED'     => ['SHIPPED'],
            'SHIPPED'    => ['OUT_FOR_DELIVERY', 'DELIVERED'],
            'OUT_FOR_DELIVERY' => ['DELIVERED'],
        ];

        if (!in_array($newStatus, $allowedTransitions[$this->status] ?? [], true)) {
            return false;
        }

        return DB::transaction(function () use ($newStatus, $processedBy): bool {
            $update = [
                'status'       => $newStatus,
                'processed_by' => $processedBy,
            ];

            $now = now();
            match ($newStatus) {
                'CONFIRMED'   => $update['confirmed_at'] = $now,
                'SHIPPED'     => $update['shipped_at']   = $now,
                'DELIVERED'   => $update['delivered_at'] = $now,
                'CANCELLED'   => $update['cancelled_at'] = $now,
                default       => null,
            };

            return $this->update($update);
        });
    }
}
