<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

final class Announcement extends Model
{
    protected $fillable = [
        'title',
        'body',
        'image_url',
        'target_roles',
        'is_pinned',
        'is_published',
        'published_at',
        'expires_at',
        'created_by',
    ];

    protected $casts = [
        'target_roles' => 'array',
        'is_pinned'    => 'boolean',
        'is_published' => 'boolean',
        'published_at' => 'datetime',
        'expires_at'   => 'datetime',
    ];

    public static function validate(array $data): array
    {
        $validator = Validator::make($data, [
            'title'        => 'required|string|max:255',
            'body'         => 'required|string',
            'image_url'    => 'nullable|string|url',
            'target_roles' => 'nullable|array',
            'target_roles.*' => 'string|in:SUPER_ADMIN,OPERATIONS,WAREHOUSE,ACCOUNTING,MARKETING,DISTRIBUTOR,RESELLER,CUSTOMER',
            'is_pinned'    => 'boolean',
            'is_published' => 'boolean',
            'published_at' => 'nullable|date',
            'expires_at'   => 'nullable|date',
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

        if (isset($filters['is_published'])) {
            $query->where('is_published', (bool) $filters['is_published']);
        }

        $announcements = $query->orderByDesc('is_pinned')->orderByDesc('created_at')->paginate((int) ($filters['per_page'] ?? 20));

        return [
            'announcements' => $announcements->items(),
            'pagination'    => [
                'current_page' => $announcements->currentPage(),
                'last_page'    => $announcements->lastPage(),
                'per_page'     => $announcements->perPage(),
                'total'        => $announcements->total(),
            ],
        ];
    }

    public function publish(string $createdBy): bool
    {
        return $this->update([
            'is_published' => true,
            'published_at' => now(),
            'created_by'   => $createdBy,
        ]);
    }
}
