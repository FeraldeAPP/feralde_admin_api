<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Validator;

final class SystemSetting extends Model
{
    public $timestamps = false;

    protected $table = 'system_settings';

    protected $fillable = [
        'key',
        'value',
        'group',
        'updated_by',
    ];

    protected $casts = [
        'value' => 'array',
    ];

    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = self::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    public static function set(string $key, mixed $value, ?string $group = null, ?string $updatedBy = null): self
    {
        return self::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group, 'updated_by' => $updatedBy]
        );
    }

    public static function validate(array $data): array
    {
        $validator = Validator::make($data, [
            'key'   => 'required|string|max:255',
            'value' => 'required',
            'group' => 'nullable|string|max:100',
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

    public static function getAll(?string $group = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = self::query();

        if ($group) {
            $query->where('group', $group);
        }

        return $query->orderBy('group')->orderBy('key')->get();
    }
}
