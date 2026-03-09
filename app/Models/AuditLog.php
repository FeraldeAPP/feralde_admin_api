<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class AuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'action',
        'resource',
        'resource_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'metadata',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'metadata'   => 'array',
    ];

    public static function record(
        ?string $userId,
        string $action,
        string $resource,
        ?string $resourceId = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $ipAddress = null
    ): self {
        return self::create([
            'user_id'     => $userId,
            'action'      => $action,
            'resource'    => $resource,
            'resource_id' => $resourceId,
            'old_values'  => $oldValues,
            'new_values'  => $newValues,
            'ip_address'  => $ipAddress,
        ]);
    }

    public static function getAll(array $filters = []): array
    {
        $query = self::query();

        if (!empty($filters['resource'])) {
            $query->where('resource', $filters['resource']);
        }

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['action'])) {
            $query->where('action', $filters['action']);
        }

        $logs = $query->orderByDesc('created_at')->paginate((int) ($filters['per_page'] ?? 30));

        return [
            'logs' => $logs->items(),
            'pagination' => [
                'current_page' => $logs->currentPage(),
                'last_page'    => $logs->lastPage(),
                'per_page'     => $logs->perPage(),
                'total'        => $logs->total(),
            ],
        ];
    }
}
