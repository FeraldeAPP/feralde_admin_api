<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

final class CommissionRule extends Model
{
    protected $fillable = [
        'name',
        'commission_type',
        'applicable_rank',
        'personal_sale_rate',
        'reseller_override_rate',
        'min_sales_volume',
        'max_sales_volume',
        'is_active',
        'effective_from',
        'effective_to',
    ];

    protected $casts = [
        'personal_sale_rate'     => 'decimal:4',
        'reseller_override_rate' => 'decimal:4',
        'min_sales_volume'       => 'decimal:4',
        'max_sales_volume'       => 'decimal:4',
        'is_active'              => 'boolean',
        'effective_from'         => 'datetime',
        'effective_to'           => 'datetime',
    ];

    public function commissions(): HasMany
    {
        return $this->hasMany(Commission::class, 'commission_rule_id');
    }

    public static function validate(array $data): array
    {
        $validator = Validator::make($data, [
            'name'                   => 'required|string|max:255',
            'commission_type'        => 'required|string|in:PERSONAL_SALE,RESELLER_OVERRIDE,RANK_BONUS,PERFORMANCE_BONUS,CHANNEL_BONUS',
            'applicable_rank'        => 'nullable|string|in:STARTER,BRONZE,SILVER,GOLD,PLATINUM,DIAMOND',
            'personal_sale_rate'     => 'nullable|numeric|min:0|max:100',
            'reseller_override_rate' => 'nullable|numeric|min:0|max:100',
            'min_sales_volume'       => 'nullable|numeric|min:0',
            'max_sales_volume'       => 'nullable|numeric|min:0',
            'is_active'              => 'boolean',
            'effective_from'         => 'required|date',
            'effective_to'           => 'nullable|date|after:effective_from',
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

    public static function createRule(array $data): self
    {
        return DB::transaction(fn(): self => self::create($data));
    }

    public static function getAll(array $filters = []): array
    {
        $query = self::query();

        if (isset($filters['is_active'])) {
            $query->where('is_active', (bool) $filters['is_active']);
        }

        if (!empty($filters['commission_type'])) {
            $query->where('commission_type', $filters['commission_type']);
        }

        $perPage = (int) ($filters['per_page'] ?? 20);
        $rules   = $query->orderByDesc('effective_from')->paginate($perPage);

        return [
            'rules' => $rules->items(),
            'pagination' => [
                'current_page' => $rules->currentPage(),
                'last_page'    => $rules->lastPage(),
                'per_page'     => $rules->perPage(),
                'total'        => $rules->total(),
            ],
        ];
    }
}
