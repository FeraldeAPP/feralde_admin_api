<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

final class Expense extends Model
{
    protected $fillable = [
        'category',
        'description',
        'amount',
        'expense_date',
        'reference_id',
        'receipt_url',
        'approved_by',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'amount'       => 'decimal:4',
        'expense_date' => 'datetime',
    ];

    public static function validate(array $data): array
    {
        $validator = Validator::make($data, [
            'category'     => 'required|string|in:COGS,SHIPPING,MARKETING,SALARIES,PLATFORM_FEES,COMMISSION_PAYOUT,UTILITIES,REFUNDS,PACKAGING,MISCELLANEOUS',
            'description'  => 'required|string|max:500',
            'amount'       => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'reference_id' => 'nullable|string|max:100',
            'receipt_url'  => 'nullable|string|url',
            'notes'        => 'nullable|string',
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

        if (!empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (!empty($filters['date_from'])) {
            $query->where('expense_date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('expense_date', '<=', $filters['date_to']);
        }

        $expenses = $query->orderByDesc('expense_date')->paginate((int) ($filters['per_page'] ?? 20));

        return [
            'expenses' => $expenses->items(),
            'pagination' => [
                'current_page' => $expenses->currentPage(),
                'last_page'    => $expenses->lastPage(),
                'per_page'     => $expenses->perPage(),
                'total'        => $expenses->total(),
            ],
        ];
    }

    public static function createExpense(array $data, string $createdBy): self
    {
        return DB::transaction(fn(): self => self::create(array_merge($data, ['created_by' => $createdBy])));
    }
}
