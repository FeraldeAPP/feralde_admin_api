<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

final class LeaderboardEntry extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'period',
        'distributor_id',
        'reseller_id',
        'total_sales',
        'total_orders',
        'rank',
        'badge',
    ];

    protected $casts = [
        'total_sales'  => 'decimal:4',
        'total_orders' => 'integer',
        'rank'         => 'integer',
    ];

    public function distributor(): BelongsTo
    {
        return $this->belongsTo(DistributorProfile::class, 'distributor_id');
    }

    public function reseller(): BelongsTo
    {
        return $this->belongsTo(ResellerProfile::class, 'reseller_id');
    }

    public static function validate(array $data): array
    {
        $validator = Validator::make($data, [
            'period'         => 'required|string|max:10',
            'distributor_id' => 'nullable|integer|exists:distributor_profiles,id',
            'reseller_id'    => 'nullable|integer|exists:reseller_profiles,id',
            'total_sales'    => 'required|numeric|min:0',
            'total_orders'   => 'required|integer|min:0',
            'rank'           => 'required|integer|min:1',
            'badge'          => 'nullable|string|max:50',
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

    public static function upsertEntry(array $data): self
    {
        return DB::transaction(function () use ($data): self {
            $key = $data['distributor_id'] ?? null
                ? ['period' => $data['period'], 'distributor_id' => $data['distributor_id']]
                : ['period' => $data['period'], 'reseller_id' => $data['reseller_id']];

            return self::updateOrCreate($key, $data);
        });
    }

    public static function getForPeriod(string $period): \Illuminate\Database\Eloquent\Collection
    {
        return self::with(['distributor', 'reseller'])
            ->where('period', $period)
            ->orderBy('rank')
            ->get();
    }
}
