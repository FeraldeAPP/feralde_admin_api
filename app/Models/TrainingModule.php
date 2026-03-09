<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

final class TrainingModule extends Model
{
    protected $fillable = [
        'title',
        'description',
        'is_published',
        'sort_order',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'sort_order'   => 'integer',
    ];

    public function contents(): HasMany
    {
        return $this->hasMany(TrainingContent::class, 'module_id')->orderBy('sort_order');
    }

    public function completions(): HasMany
    {
        return $this->hasMany(TrainingCompletion::class, 'module_id');
    }

    public static function validate(array $data): array
    {
        $validator = Validator::make($data, [
            'title'        => 'required|string|max:255',
            'description'  => 'nullable|string',
            'is_published' => 'boolean',
            'sort_order'   => 'integer|min:0',
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
        $query = self::withCount('contents');

        if (isset($filters['is_published'])) {
            $query->where('is_published', (bool) $filters['is_published']);
        }

        $modules = $query->orderBy('sort_order')->paginate((int) ($filters['per_page'] ?? 20));

        return [
            'modules' => $modules->items(),
            'pagination' => [
                'current_page' => $modules->currentPage(),
                'last_page'    => $modules->lastPage(),
                'per_page'     => $modules->perPage(),
                'total'        => $modules->total(),
            ],
        ];
    }

    public static function createModule(array $data): self
    {
        return DB::transaction(fn(): self => self::create($data));
    }
}
