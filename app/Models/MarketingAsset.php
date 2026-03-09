<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Validator;

final class MarketingAsset extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'title',
        'type',
        'url',
        'thumbnail_url',
        'description',
        'tags',
        'is_active',
        'uploaded_by',
    ];

    protected $casts = [
        'tags'      => 'array',
        'is_active' => 'boolean',
    ];

    public static function validate(array $data): array
    {
        $validator = Validator::make($data, [
            'title'         => 'required|string|max:255',
            'type'          => 'required|string|in:IMAGE,VIDEO,DOCUMENT,PDF',
            'url'           => 'required|string|url',
            'thumbnail_url' => 'nullable|string|url',
            'description'   => 'nullable|string',
            'tags'          => 'nullable|array',
            'tags.*'        => 'string',
            'is_active'     => 'boolean',
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

        if (isset($filters['is_active'])) {
            $query->where('is_active', (bool) $filters['is_active']);
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['search'])) {
            $query->where('title', 'like', '%' . $filters['search'] . '%');
        }

        $assets  = $query->orderByDesc('created_at')->paginate((int) ($filters['per_page'] ?? 20));

        return [
            'assets' => $assets->items(),
            'pagination' => [
                'current_page' => $assets->currentPage(),
                'last_page'    => $assets->lastPage(),
                'per_page'     => $assets->perPage(),
                'total'        => $assets->total(),
            ],
        ];
    }
}
