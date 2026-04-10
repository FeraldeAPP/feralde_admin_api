<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingMethod extends Model
{
    protected $fillable = [
        'name',
        'description',
        'base_fee',
        'is_active',
    ];

    protected $casts = [
        'base_fee' => 'decimal:2',
        'is_active' => 'boolean',
    ];
}
