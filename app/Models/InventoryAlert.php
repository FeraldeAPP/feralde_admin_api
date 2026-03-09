<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class InventoryAlert extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'inventory_id',
        'type',
        'threshold',
        'current_level',
        'is_resolved',
        'resolved_at',
        'resolved_by',
    ];

    protected $casts = [
        'is_resolved'   => 'boolean',
        'current_level' => 'integer',
        'threshold'     => 'integer',
        'resolved_at'   => 'datetime',
    ];

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class);
    }

    public function resolve(string $resolvedBy): bool
    {
        return $this->update([
            'is_resolved' => true,
            'resolved_at' => now(),
            'resolved_by' => $resolvedBy,
        ]);
    }

    public static function getUnresolved(array $filters = []): array
    {
        $query = self::with(['inventory.variant.product', 'inventory.warehouse'])
            ->where('is_resolved', false);

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        $perPage = (int) ($filters['per_page'] ?? 30);
        $alerts  = $query->orderByDesc('created_at')->paginate($perPage);

        return [
            'alerts' => $alerts->items(),
            'pagination' => [
                'current_page' => $alerts->currentPage(),
                'last_page'    => $alerts->lastPage(),
                'per_page'     => $alerts->perPage(),
                'total'        => $alerts->total(),
            ],
        ];
    }
}
