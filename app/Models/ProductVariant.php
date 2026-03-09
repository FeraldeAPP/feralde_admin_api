<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

final class ProductVariant extends Model
{
    protected $fillable = [
        'product_id',
        'sku',
        'name',
        'size',
        'concentration',
        'barcode',
        'weight_grams',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active'    => 'boolean',
        'sort_order'   => 'integer',
        'weight_grams' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function pricing(): HasMany
    {
        return $this->hasMany(VariantPricing::class, 'variant_id');
    }

    public function inventory(): HasOne
    {
        return $this->hasOne(Inventory::class, 'variant_id');
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'variant_id');
    }

    public static function validate(array $data, ?int $excludeId = null): array
    {
        $skuRule = $excludeId ? "required|string|max:100|unique:product_variants,sku,{$excludeId}" : 'required|string|max:100|unique:product_variants,sku';

        $validator = Validator::make($data, [
            'product_id'    => 'required|integer|exists:products,id',
            'sku'           => $skuRule,
            'name'          => 'required|string|max:255',
            'size'          => 'nullable|string|max:50',
            'concentration' => 'nullable|string|max:100',
            'barcode'       => 'nullable|string|max:100',
            'weight_grams'  => 'nullable|integer|min:1',
            'is_active'     => 'boolean',
            'sort_order'    => 'integer|min:0',
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

    public static function createVariant(array $data): self
    {
        return DB::transaction(fn(): self => self::create($data));
    }

    public static function updateVariant(int $id, array $data): ?self
    {
        $variant = self::find($id);
        if (!$variant) {
            return null;
        }

        DB::transaction(function () use ($variant, $data): void {
            $variant->update($data);
        });

        return $variant->fresh(['pricing', 'inventory']);
    }

    public static function deleteVariant(int $id): bool
    {
        $variant = self::find($id);
        if (!$variant) {
            return false;
        }

        $variant->delete();
        return true;
    }
}
