<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ChannelProduct extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'channel_id',
        'variant_id',
        'external_listing_id',
        'external_sku',
        'external_price',
        'external_stock',
        'last_synced_at',
        'is_active',
    ];

    protected $casts = [
        'external_price'  => 'decimal:4',
        'external_stock'  => 'integer',
        'is_active'       => 'boolean',
        'last_synced_at'  => 'datetime',
    ];

    public function channel(): BelongsTo
    {
        return $this->belongsTo(SalesChannel::class, 'channel_id');
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }
}
