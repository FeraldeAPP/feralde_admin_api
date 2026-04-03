<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

final class Inventory extends Model
{
    public $timestamps = false;

    protected $table = 'inventory';

    protected $fillable = [
        'variant_id',
        'warehouse_id',
        'quantity_on_hand',
        'quantity_reserved',
        'quantity_damaged',
        'reorder_point',
        'reorder_quantity',
        'last_stocked_at',
        'last_audited_at',
    ];

    protected $casts = [
        'quantity_on_hand'  => 'integer',
        'quantity_reserved' => 'integer',
        'quantity_damaged'  => 'integer',
        'reorder_point'     => 'integer',
        'reorder_quantity'  => 'integer',
        'last_stocked_at'   => 'datetime',
        'last_audited_at'   => 'datetime',
    ];

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(InventoryAlert::class);
    }

    public function getAvailableQuantityAttribute(): int
    {
        return max(0, $this->quantity_on_hand - $this->quantity_reserved);
    }

    public static function adjustStock(int $variantId, int $warehouseId, int $qty, string $type, string $notes = '', ?string $performedBy = null): self
    {
        return DB::transaction(function () use ($variantId, $warehouseId, $qty, $type, $notes, $performedBy): self {
            $inventory = self::firstOrCreate(
                ['variant_id' => $variantId, 'warehouse_id' => $warehouseId],
                ['quantity_on_hand' => 0, 'quantity_reserved' => 0, 'quantity_damaged' => 0]
            );

            $before = $inventory->quantity_on_hand;
            $after  = max(0, $before + $qty);

            $inventory->update([
                'quantity_on_hand' => $after,
                'last_stocked_at'  => now(),
            ]);

            StockMovement::create([
                'variant_id'      => $variantId,
                'warehouse_id'    => $warehouseId,
                'type'            => $type,
                'quantity'        => $qty,
                'quantity_before' => $before,
                'quantity_after'  => $after,
                'notes'           => $notes,
                'performed_by'    => $performedBy,
            ]);

            if ($after <= $inventory->reorder_point) {
                InventoryAlert::firstOrCreate(
                    ['inventory_id' => $inventory->id, 'type' => $after === 0 ? 'OUT_OF_STOCK' : 'LOW_STOCK', 'is_resolved' => false],
                    ['current_level' => $after, 'threshold' => $inventory->reorder_point]
                );
            }

            return $inventory->fresh();
        });
    }

    public static function getAll(array $filters = []): array
    {
        $query = self::with(['variant.product.media', 'warehouse']);

        if (!empty($filters['warehouse_id'])) {
            $query->where('warehouse_id', $filters['warehouse_id']);
        }

        if (isset($filters['low_stock'])) {
            $query->whereRaw('quantity_on_hand <= reorder_point');
        }

        $perPage   = (int) ($filters['per_page'] ?? 30);
        $inventory = $query->paginate($perPage);

        return [
            'inventory' => $inventory->items(),
            'pagination' => [
                'current_page' => $inventory->currentPage(),
                'last_page'    => $inventory->lastPage(),
                'per_page'     => $inventory->perPage(),
                'total'        => $inventory->total(),
            ],
        ];
    }
}
