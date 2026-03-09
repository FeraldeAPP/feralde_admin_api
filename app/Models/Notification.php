<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class Notification extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'body',
        'data',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'data'    => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    public static function send(string $userId, string $type, string $title, ?string $body = null, ?array $data = null): self
    {
        return self::create([
            'user_id' => $userId,
            'type'    => $type,
            'title'   => $title,
            'body'    => $body,
            'data'    => $data,
        ]);
    }

    public static function getAll(array $filters = []): array
    {
        $query = self::query();

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (isset($filters['is_read'])) {
            $query->where('is_read', (bool) $filters['is_read']);
        }

        $notifications = $query->orderByDesc('created_at')->paginate((int) ($filters['per_page'] ?? 30));

        return [
            'notifications' => $notifications->items(),
            'pagination'    => [
                'current_page' => $notifications->currentPage(),
                'last_page'    => $notifications->lastPage(),
                'per_page'     => $notifications->perPage(),
                'total'        => $notifications->total(),
            ],
        ];
    }
}
