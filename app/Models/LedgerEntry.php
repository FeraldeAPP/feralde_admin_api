<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Validator;

final class LedgerEntry extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'period_id',
        'entry_date',
        'entry_type',
        'category',
        'channel_id',
        'reference_type',
        'reference_id',
        'description',
        'amount',
        'debit',
        'credit',
        'is_reconciled',
        'created_by',
    ];

    protected $casts = [
        'amount'        => 'decimal:4',
        'debit'         => 'decimal:4',
        'credit'        => 'decimal:4',
        'is_reconciled' => 'boolean',
        'entry_date'    => 'datetime',
    ];

    public function period(): BelongsTo
    {
        return $this->belongsTo(AccountingPeriod::class, 'period_id');
    }

    public static function validate(array $data): array
    {
        $validator = Validator::make($data, [
            'period_id'      => 'required|integer|exists:accounting_periods,id',
            'entry_date'     => 'required|date',
            'entry_type'     => 'required|string|max:50',
            'category'       => 'nullable|string|in:COGS,SHIPPING,MARKETING,SALARIES,PLATFORM_FEES,COMMISSION_PAYOUT,UTILITIES,REFUNDS,PACKAGING,MISCELLANEOUS',
            'description'    => 'required|string|max:500',
            'amount'         => 'required|numeric|min:0',
            'debit'          => 'nullable|numeric|min:0',
            'credit'         => 'nullable|numeric|min:0',
            'reference_type' => 'nullable|string|max:50',
            'reference_id'   => 'nullable|string|max:100',
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
}
