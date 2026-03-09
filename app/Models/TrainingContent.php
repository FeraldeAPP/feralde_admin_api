<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Validator;

final class TrainingContent extends Model
{
    protected $fillable = [
        'module_id',
        'title',
        'type',
        'content_url',
        'body',
        'duration_min',
        'sort_order',
        'is_required',
    ];

    protected $casts = [
        'duration_min' => 'integer',
        'sort_order'   => 'integer',
        'is_required'  => 'boolean',
    ];

    public function module(): BelongsTo
    {
        return $this->belongsTo(TrainingModule::class, 'module_id');
    }

    public static function validate(array $data): array
    {
        $validator = Validator::make($data, [
            'module_id'   => 'required|integer|exists:training_modules,id',
            'title'       => 'required|string|max:255',
            'type'        => 'required|string|in:VIDEO,ARTICLE,QUIZ,PDF,CERTIFICATION',
            'content_url' => 'nullable|string|url',
            'body'        => 'nullable|string',
            'duration_min' => 'nullable|integer|min:1',
            'sort_order'  => 'integer|min:0',
            'is_required' => 'boolean',
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
}
