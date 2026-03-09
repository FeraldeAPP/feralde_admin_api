<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ProductReview extends Model
{
    protected $fillable = [
        'product_id',
        'user_id',
        'order_id',
        'rating',
        'title',
        'body',
        'is_verified',
        'is_approved',
    ];

    protected $casts = [
        'rating'      => 'integer',
        'is_verified' => 'boolean',
        'is_approved' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public static function getAll(array $filters = []): array
    {
        $query = self::with('product');

        if (isset($filters['is_approved'])) {
            $query->where('is_approved', (bool) $filters['is_approved']);
        }

        if (!empty($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }

        $perPage = (int) ($filters['per_page'] ?? 20);
        $reviews = $query->orderByDesc('created_at')->paginate($perPage);

        return [
            'reviews' => $reviews->items(),
            'pagination' => [
                'current_page' => $reviews->currentPage(),
                'last_page'    => $reviews->lastPage(),
                'per_page'     => $reviews->perPage(),
                'total'        => $reviews->total(),
            ],
        ];
    }

    public function approve(): bool
    {
        return $this->update(['is_approved' => true]);
    }

    public function reject(): bool
    {
        return $this->update(['is_approved' => false]);
    }
}
