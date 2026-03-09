<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

final class Category extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'image_url',
        'parent_id',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public static function validate(array $data, ?int $excludeId = null): array
    {
        $slugRule = $excludeId ? "required|string|unique:categories,slug,{$excludeId}" : 'required|string|unique:categories,slug';

        $validator = Validator::make($data, [
            'name'        => 'required|string|max:255',
            'slug'        => $slugRule,
            'description' => 'nullable|string',
            'image_url'   => 'nullable|string|url',
            'parent_id'   => 'nullable|integer|exists:categories,id',
            'sort_order'  => 'integer|min:0',
            'is_active'   => 'boolean',
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
        $query = self::withCount('products');

        if (!empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', (bool) $filters['is_active']);
        }

        if (isset($filters['parent_id'])) {
            $query->where('parent_id', $filters['parent_id'] ?: null);
        }

        $perPage    = (int) ($filters['per_page'] ?? 30);
        $categories = $query->orderBy('sort_order')->paginate($perPage);

        return [
            'categories' => $categories->items(),
            'pagination' => [
                'current_page' => $categories->currentPage(),
                'last_page'    => $categories->lastPage(),
                'per_page'     => $categories->perPage(),
                'total'        => $categories->total(),
            ],
        ];
    }

    public static function createCategory(array $data): self
    {
        return DB::transaction(fn(): self => self::create($data));
    }

    public static function updateCategory(int $id, array $data): ?self
    {
        $category = self::find($id);
        if (!$category) {
            return null;
        }

        DB::transaction(function () use ($category, $data): void {
            $category->update($data);
        });

        return $category->fresh();
    }

    public static function deleteCategory(int $id): bool
    {
        $category = self::find($id);
        if (!$category) {
            return false;
        }

        $category->delete();
        return true;
    }
}
