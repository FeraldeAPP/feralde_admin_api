<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class Address extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'phone',
        'region',
        'province',
        'city',
        'barangay',
        'details',
        'postal_code',
        'country',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public static function getAll(array $filters = []): array
    {
        $query = self::query();

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        $perPage   = (int) ($filters['per_page'] ?? 20);
        $addresses = $query->orderByDesc('id')->paginate($perPage);

        return [
            'addresses' => $addresses->items(),
            'pagination' => [
                'current_page' => $addresses->currentPage(),
                'last_page'    => $addresses->lastPage(),
                'per_page'     => $addresses->perPage(),
                'total'        => $addresses->total(),
            ],
        ];
    }
}
