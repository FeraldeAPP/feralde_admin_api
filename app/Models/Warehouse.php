<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

final class Warehouse extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
        'code',
        'address',
        'is_default',
        'is_active',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active'  => 'boolean',
    ];

    public function inventory(): HasMany
    {
        return $this->hasMany(Inventory::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public static function validate(array $data, ?int $excludeId = null): array
    {
        $codeRule = $excludeId ? "required|string|max:50|unique:warehouses,code,{$excludeId}" : 'required|string|max:50|unique:warehouses,code';

        $validator = Validator::make($data, [
            'name'       => 'required|string|max:255',
            'code'       => $codeRule,
            'address'    => 'nullable|string',
            'is_default' => 'boolean',
            'is_active'  => 'boolean',
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

    public static function createWarehouse(array $data): self
    {
        return DB::transaction(function () use ($data): self {
            if (!empty($data['is_default'])) {
                self::where('is_default', true)->update(['is_default' => false]);
            }

            return self::create($data);
        });
    }

    public static function updateWarehouse(int $id, array $data): ?self
    {
        $warehouse = self::find($id);
        if (!$warehouse) {
            return null;
        }

        DB::transaction(function () use ($warehouse, $data): void {
            if (!empty($data['is_default'])) {
                self::where('id', '!=', $warehouse->id)->update(['is_default' => false]);
            }

            $warehouse->update($data);
        });

        return $warehouse->fresh();
    }
}
