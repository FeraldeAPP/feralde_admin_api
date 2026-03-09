<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

final class Invoice extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'order_id',
        'invoice_number',
        'issued_at',
        'due_at',
        'subtotal',
        'tax',
        'total',
        'pdf_url',
        'notes',
    ];

    protected $casts = [
        'subtotal'  => 'decimal:4',
        'tax'       => 'decimal:4',
        'total'     => 'decimal:4',
        'issued_at' => 'datetime',
        'due_at'    => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public static function generateNumber(): string
    {
        return 'INV-' . strtoupper(uniqid('', true));
    }

    public static function createForOrder(int $orderId, array $data): self
    {
        return DB::transaction(fn(): self => self::create(array_merge(
            $data,
            ['order_id' => $orderId, 'invoice_number' => self::generateNumber()]
        )));
    }
}
