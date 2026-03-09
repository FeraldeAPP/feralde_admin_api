<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class FinancialSummary extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'period_id',
        'channel_type',
        'gross_revenue',
        'net_revenue',
        'total_cogs',
        'gross_profit',
        'total_expenses',
        'total_commissions',
        'total_refunds',
        'total_shipping_fees',
        'net_income',
        'total_orders',
    ];

    protected $casts = [
        'gross_revenue'       => 'decimal:4',
        'net_revenue'         => 'decimal:4',
        'total_cogs'          => 'decimal:4',
        'gross_profit'        => 'decimal:4',
        'total_expenses'      => 'decimal:4',
        'total_commissions'   => 'decimal:4',
        'total_refunds'       => 'decimal:4',
        'total_shipping_fees' => 'decimal:4',
        'net_income'          => 'decimal:4',
        'total_orders'        => 'integer',
    ];

    public function period(): BelongsTo
    {
        return $this->belongsTo(AccountingPeriod::class, 'period_id');
    }
}
