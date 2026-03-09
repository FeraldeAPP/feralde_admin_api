<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class StockMovement extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'variant_id',
        'warehouse_id',
        'type',
        'quantity',
        'quantity_before',
        'quantity_after',
        'reference_type',
        'reference_id',
        'notes',
        'performed_by',
    ];

    protected $casts = [
        'quantity'        => 'integer',
        'quantity_before' => 'integer',
        'quantity_after'  => 'integer',
    ];

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public static function getAll(array $filters = []): array
    {
        $query = self::with(['variant.product', 'warehouse']);

        if (!empty($filters['variant_id'])) {
            $query->where('variant_id', $filters['variant_id']);
        }

        if (!empty($filters['warehouse_id'])) {
            $query->where('warehouse_id', $filters['warehouse_id']);
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        $perPage   = (int) ($filters['per_page'] ?? 30);
        $movements = $query->orderByDesc('created_at')->paginate($perPage);

        return [
            'movements' => $movements->items(),
            'pagination' => [
                'current_page' => $movements->currentPage(),
                'last_page'    => $movements->lastPage(),
                'per_page'     => $movements->perPage(),
                'total'        => $movements->total(),
            ],
        ];
    }
}
