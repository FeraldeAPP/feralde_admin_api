<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

final class Bundle extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'type',
        'image_url',
        'retail_price',
        'distributor_price',
        'is_active',
        'starts_at',
        'ends_at',
    ];

    protected $casts = [
        'retail_price'      => 'decimal:4',
        'distributor_price' => 'decimal:4',
        'is_active'         => 'boolean',
        'starts_at'         => 'datetime',
        'ends_at'           => 'datetime',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(BundleItem::class);
    }

    public static function validate(array $data, ?int $excludeId = null): array
    {
        $slugRule = $excludeId ? "required|string|unique:bundles,slug,{$excludeId}" : 'required|string|unique:bundles,slug';

        $validator = Validator::make($data, [
            'name'              => 'required|string|max:255',
            'slug'              => $slugRule,
            'description'       => 'nullable|string',
            'type'              => 'required|string|in:FIXED,DYNAMIC,GIFT_SET',
            'image_url'         => 'nullable|string|url',
            'retail_price'      => 'required|numeric|min:0',
            'distributor_price' => 'nullable|numeric|min:0',
            'is_active'         => 'boolean',
            'starts_at'         => 'nullable|date',
            'ends_at'           => 'nullable|date|after:starts_at',
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

    public static function getAll(array $filters = []): array
    {
        $query = self::withCount('items');

        if (isset($filters['is_active'])) {
            $query->where('is_active', (bool) $filters['is_active']);
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        $perPage = (int) ($filters['per_page'] ?? 20);
        $bundles = $query->orderByDesc('created_at')->paginate($perPage);

        return [
            'bundles' => $bundles->items(),
            'pagination' => [
                'current_page' => $bundles->currentPage(),
                'last_page'    => $bundles->lastPage(),
                'per_page'     => $bundles->perPage(),
                'total'        => $bundles->total(),
            ],
        ];
    }

    public static function createBundle(array $data): self
    {
        return DB::transaction(fn(): self => self::create($data));
    }
}
