<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

final class Fulfillment extends Model
{
    protected $fillable = [
        'order_id',
        'courier_name',
        'tracking_number',
        'tracking_url',
        'shipped_at',
        'estimated_delivery',
        'delivered_at',
        'packed_by',
        'shipped_by',
        'notes',
    ];

    protected $casts = [
        'shipped_at'         => 'datetime',
        'estimated_delivery' => 'datetime',
        'delivered_at'       => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public static function validate(array $data): array
    {
        $validator = Validator::make($data, [
            'order_id'           => 'required|integer|exists:orders,id',
            'courier_name'       => 'nullable|string|max:100',
            'tracking_number'    => 'nullable|string|max:100',
            'tracking_url'       => 'nullable|string|url',
            'shipped_at'         => 'nullable|date',
            'estimated_delivery' => 'nullable|date',
            'packed_by'          => 'nullable|string|max:100',
            'shipped_by'         => 'nullable|string|max:100',
            'notes'              => 'nullable|string',
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

    public static function createForOrder(array $data): self
    {
        return DB::transaction(fn(): self => self::create($data));
    }
}
