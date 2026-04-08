<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class OrderHistory extends Model
{
    protected $fillable = [
        'order_id',
        'user_id',
        'user_name',
        'action',
        'description',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'json',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
