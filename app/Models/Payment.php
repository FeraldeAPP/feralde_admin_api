<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

final class Payment extends Model
{
    protected $fillable = [
        'order_id',
        'method',
        'status',
        'amount',
        'currency',
        'gateway_reference',
        'gateway_response',
        'paid_at',
        'failed_at',
        'refunded_at',
        'refunded_amount',
        'notes',
    ];

    protected $casts = [
        'amount'           => 'decimal:4',
        'refunded_amount'  => 'decimal:4',
        'gateway_response' => 'array',
        'paid_at'          => 'datetime',
        'failed_at'        => 'datetime',
        'refunded_at'      => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function markPaid(?string $gatewayReference = null): bool
    {
        return DB::transaction(function () use ($gatewayReference): bool {
            $result = $this->update([
                'status'            => 'PAID',
                'paid_at'           => now(),
                'gateway_reference' => $gatewayReference ?? $this->gateway_reference,
            ]);

            $this->order->update(['payment_status' => 'PAID']);

            return $result;
        });
    }

    public function markRefunded(float $refundedAmount): bool
    {
        return DB::transaction(function () use ($refundedAmount): bool {
            $status = $refundedAmount >= (float) $this->amount ? 'REFUNDED' : 'PARTIALLY_REFUNDED';

            $result = $this->update([
                'status'          => $status,
                'refunded_at'     => now(),
                'refunded_amount' => $refundedAmount,
            ]);

            $this->order->update(['payment_status' => $status]);

            return $result;
        });
    }
}
