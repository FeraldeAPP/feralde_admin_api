<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

final class VariantPricing extends Model
{
    public $timestamps = false;

    protected $table = 'variant_pricing';

    protected $fillable = [
        'variant_id',
        'tier',
        'price',
        'compare_at_price',
        'min_quantity',
        'is_active',
        'effective_from',
        'effective_to',
    ];

    protected $casts = [
        'price'            => 'decimal:4',
        'compare_at_price' => 'decimal:4',
        'min_quantity'     => 'integer',
        'is_active'        => 'boolean',
        'effective_from'   => 'datetime',
        'effective_to'     => 'datetime',
    ];

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public static function validate(array $data): array
    {
        $validator = Validator::make($data, [
            'variant_id'       => 'required|integer|exists:product_variants,id',
            'tier'             => 'required|string|in:RETAIL,DISTRIBUTOR,RESELLER,WHOLESALE',
            'price'            => 'required|numeric|min:0',
            'compare_at_price' => 'nullable|numeric|min:0',
            'min_quantity'     => 'integer|min:1',
            'is_active'        => 'boolean',
            'effective_from'   => 'sometimes|date',
            'effective_to'     => 'nullable|date|after:effective_from',
        ]);

        if ($validator->fails()) {
            throw new HttpResponseException(response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors(),
            ], 422));
        }

        return $validator->validated();
    }

    public static function upsertPricing(int $variantId, string $tier, array $data): self
    {
        return DB::transaction(fn(): self => self::updateOrCreate(
            ['variant_id' => $variantId, 'tier' => $tier],
            $data
        ));
    }
}
