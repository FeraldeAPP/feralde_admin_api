<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Validator;

final class OrderReturn extends Model
{
    protected $table = 'returns';

    protected $fillable = [
        'order_id',
        'reason',
        'status',
        'refund_amount',
        'refund_method',
        'notes',
        'processed_by',
    ];

    protected $casts = [
        'refund_amount' => 'decimal:4',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public static function validate(array $data): array
    {
        $validator = Validator::make($data, [
            'order_id'      => 'required|integer|exists:orders,id',
            'reason'        => 'required|string|max:1000',
            'status'        => 'sometimes|string|in:PENDING,APPROVED,REJECTED,COMPLETED',
            'refund_amount' => 'nullable|numeric|min:0',
            'refund_method' => 'nullable|string|max:50',
            'notes'         => 'nullable|string',
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
