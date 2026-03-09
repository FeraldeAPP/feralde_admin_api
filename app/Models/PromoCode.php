<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

final class PromoCode extends Model
{
    protected $fillable = [
        'code',
        'description',
        'type',
        'value',
        'min_order_amount',
        'max_discount',
        'usage_limit',
        'usage_count',
        'per_user_limit',
        'is_active',
        'starts_at',
        'ends_at',
    ];

    protected $casts = [
        'value'            => 'decimal:4',
        'min_order_amount' => 'decimal:4',
        'max_discount'     => 'decimal:4',
        'usage_limit'      => 'integer',
        'usage_count'      => 'integer',
        'per_user_limit'   => 'integer',
        'is_active'        => 'boolean',
        'starts_at'        => 'datetime',
        'ends_at'          => 'datetime',
    ];

    public static function validate(array $data, ?int $excludeId = null): array
    {
        $codeRule = $excludeId ? "required|string|max:50|unique:promo_codes,code,{$excludeId}" : 'required|string|max:50|unique:promo_codes,code';

        $validator = Validator::make($data, [
            'code'             => $codeRule,
            'description'      => 'nullable|string',
            'type'             => 'required|string|in:PERCENTAGE_DISCOUNT,FIXED_DISCOUNT,FREE_SHIPPING,BUY_X_GET_Y,BUNDLE_DEAL',
            'value'            => 'required|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_discount'     => 'nullable|numeric|min:0',
            'usage_limit'      => 'nullable|integer|min:1',
            'per_user_limit'   => 'nullable|integer|min:1',
            'is_active'        => 'boolean',
            'starts_at'        => 'nullable|date',
            'ends_at'          => 'nullable|date|after:starts_at',
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
        $query = self::query();

        if (isset($filters['is_active'])) {
            $query->where('is_active', (bool) $filters['is_active']);
        }

        if (!empty($filters['search'])) {
            $query->where('code', 'like', '%' . $filters['search'] . '%');
        }

        $perPage = (int) ($filters['per_page'] ?? 20);
        $promos  = $query->orderByDesc('created_at')->paginate($perPage);

        return [
            'promo_codes' => $promos->items(),
            'pagination'  => [
                'current_page' => $promos->currentPage(),
                'last_page'    => $promos->lastPage(),
                'per_page'     => $promos->perPage(),
                'total'        => $promos->total(),
            ],
        ];
    }

    public static function createPromo(array $data): self
    {
        $data['code'] = strtoupper($data['code']);
        return DB::transaction(fn(): self => self::create($data));
    }
}
