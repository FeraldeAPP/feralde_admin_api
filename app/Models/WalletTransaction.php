<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class WalletTransaction extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'wallet_id',
        'type',
        'amount',
        'balance_before',
        'balance_after',
        'reference_type',
        'reference_id',
        'description',
        'metadata',
    ];

    protected $casts = [
        'amount'         => 'decimal:4',
        'balance_before' => 'decimal:4',
        'balance_after'  => 'decimal:4',
        'metadata'       => 'array',
    ];

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }
}
