<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class OrderItem extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'order_id',
        'variant_id',
        'product_id',
        'bundle_id',
        'product_name',
        'variant_name',
        'sku',
        'quantity',
        'unit_price',
        'discount_amount',
        'total_price',
        'cost_price',
        'pricing_tier',
        'commissionable_amount',
    ];

    protected $casts = [
        'quantity'              => 'integer',
        'unit_price'            => 'decimal:4',
        'discount_amount'       => 'decimal:4',
        'total_price'           => 'decimal:4',
        'cost_price'            => 'decimal:4',
        'commissionable_amount' => 'decimal:4',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
