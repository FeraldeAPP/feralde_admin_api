<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Validator;

final class ProductMedia extends Model
{
    public $timestamps = false;

    protected $table = 'product_media';

    protected $fillable = [
        'product_id',
        'type',
        'url',
        'alt_text',
        'sort_order',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public static function validate(array $data): array
    {
        $validator = Validator::make($data, [
            'product_id' => 'required|integer|exists:products,id',
            'type'       => 'required|string|in:IMAGE,VIDEO,DOCUMENT,PDF',
            'url'        => 'required|string|url',
            'alt_text'   => 'nullable|string|max:255',
            'sort_order' => 'integer|min:0',
            'is_primary' => 'boolean',
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

    public static function addMedia(array $data): self
    {
        if (!empty($data['is_primary'])) {
            self::where('product_id', $data['product_id'])->update(['is_primary' => false]);
        }

        return self::create($data);
    }
}
