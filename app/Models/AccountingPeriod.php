<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

final class AccountingPeriod extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'year',
        'month',
        'is_closed',
        'closed_at',
        'closed_by',
    ];

    protected $casts = [
        'year'      => 'integer',
        'month'     => 'integer',
        'is_closed' => 'boolean',
        'closed_at' => 'datetime',
    ];

    public function entries(): HasMany
    {
        return $this->hasMany(LedgerEntry::class, 'period_id');
    }

    public function summaries(): HasMany
    {
        return $this->hasMany(FinancialSummary::class, 'period_id');
    }

    public static function validate(array $data): array
    {
        $validator = Validator::make($data, [
            'year'  => 'required|integer|min:2020|max:2100',
            'month' => 'required|integer|min:1|max:12',
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

    public static function getOrCreate(int $year, int $month): self
    {
        return self::firstOrCreate(
            ['year' => $year, 'month' => $month],
            ['is_closed' => false]
        );
    }

    public function close(string $closedBy): bool
    {
        return DB::transaction(function () use ($closedBy): bool {
            return $this->update([
                'is_closed' => true,
                'closed_at' => now(),
                'closed_by' => $closedBy,
            ]);
        });
    }

    public static function getAll(array $filters = []): array
    {
        $query = self::withCount('entries');

        if (isset($filters['is_closed'])) {
            $query->where('is_closed', (bool) $filters['is_closed']);
        }

        $periods = $query->orderByDesc('year')->orderByDesc('month')->paginate((int) ($filters['per_page'] ?? 24));

        return [
            'periods' => $periods->items(),
            'pagination' => [
                'current_page' => $periods->currentPage(),
                'last_page'    => $periods->lastPage(),
                'per_page'     => $periods->perPage(),
                'total'        => $periods->total(),
            ],
        ];
    }
}
