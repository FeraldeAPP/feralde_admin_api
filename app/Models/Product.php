<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

final class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'sku',
        'name',
        'slug',
        'description',
        'short_description',
        'category_id',
        'scent_notes',
        'ingredients',
        'is_active',
        'is_featured',
        'is_best_seller',
        'is_new_arrival',
        'meta_title',
        'meta_description',
    ];

    protected $casts = [
        'scent_notes'    => 'array',
        'is_active'      => 'boolean',
        'is_featured'    => 'boolean',
        'is_best_seller' => 'boolean',
        'is_new_arrival' => 'boolean',
    ];

    // --- Relationships ---

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(ProductMedia::class)->orderBy('sort_order');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    // --- Validation ---

    public static function validateStore(array $data): array
    {
        $validator = Validator::make($data, [
            'sku'               => 'required|string|max:100|unique:products,sku',
            'name'              => 'required|string|max:255',
            'slug'              => 'required|string|max:255|unique:products,slug',
            'description'       => 'nullable|string',
            'short_description' => 'nullable|string|max:500',
            'category_id'       => 'nullable|integer|exists:categories,id',
            'scent_notes'       => 'nullable|array',
            'ingredients'       => 'nullable|string',
            'is_active'         => 'boolean',
            'is_featured'       => 'boolean',
            'is_best_seller'    => 'boolean',
            'is_new_arrival'    => 'boolean',
            'meta_title'        => 'nullable|string|max:255',
            'meta_description'  => 'nullable|string|max:500',
        ], [
            'sku.required'  => 'SKU is required',
            'sku.unique'    => 'SKU already exists',
            'name.required' => 'Product name is required',
            'slug.required' => 'Slug is required',
            'slug.unique'   => 'Slug already exists',
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

    public static function validateUpdate(array $data, int $productId): array
    {
        $validator = Validator::make($data, [
            'sku'               => "sometimes|string|max:100|unique:products,sku,{$productId}",
            'name'              => 'sometimes|string|max:255',
            'slug'              => "sometimes|string|max:255|unique:products,slug,{$productId}",
            'description'       => 'nullable|string',
            'short_description' => 'nullable|string|max:500',
            'category_id'       => 'nullable|integer|exists:categories,id',
            'scent_notes'       => 'nullable|array',
            'ingredients'       => 'nullable|string',
            'is_active'         => 'boolean',
            'is_featured'       => 'boolean',
            'is_best_seller'    => 'boolean',
            'is_new_arrival'    => 'boolean',
            'meta_title'        => 'nullable|string|max:255',
            'meta_description'  => 'nullable|string|max:500',
        ], [
            'sku.unique'  => 'SKU already exists',
            'slug.unique' => 'Slug already exists',
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

    // --- Business logic ---

    /**
     * @param array<string, mixed> $filters
     * @return array<string, mixed>
     */
    public static function getProducts(array $filters = []): array
    {
        $query = self::with(['category', 'variants'])->withTrashed();

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function (Builder $q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', (bool) $filters['is_active']);
        }

        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (isset($filters['deleted']) && $filters['deleted']) {
            $query->onlyTrashed();
        }

        $perPage  = (int) ($filters['per_page'] ?? 15);
        $products = $query->orderByDesc('id')->paginate($perPage);

        return [
            'products' => $products->items(),
            'pagination' => [
                'current_page' => $products->currentPage(),
                'last_page'    => $products->lastPage(),
                'per_page'     => $products->perPage(),
                'total'        => $products->total(),
            ],
        ];
    }

    public static function getProductById(int $id): ?self
    {
        return self::with(['category', 'variants.pricing', 'media', 'reviews'])->withTrashed()->find($id);
    }

    public static function createProduct(array $data): self
    {
        return DB::transaction(fn(): self => self::create($data));
    }

    public static function updateProductById(int $id, array $data): ?self
    {
        $product = self::withTrashed()->find($id);

        if (!$product) {
            return null;
        }

        DB::transaction(function () use ($product, $data): void {
            $product->update($data);
        });

        return $product->fresh();
    }

    public static function deleteProductById(int $id): bool
    {
        $product = self::find($id);

        if (!$product) {
            return false;
        }

        $product->delete();
        return true;
    }

    public static function restoreProductById(int $id): bool
    {
        $product = self::onlyTrashed()->find($id);

        if (!$product) {
            return false;
        }

        $product->restore();
        return true;
    }
}
