<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class TrainingCompletion extends Model
{
    public $timestamps = false;

    protected $table = 'training_completions';

    protected $fillable = [
        'module_id',
        'user_id',
        'completed_at',
        'score',
        'certified',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
        'score'        => 'integer',
        'certified'    => 'boolean',
    ];

    public function module(): BelongsTo
    {
        return $this->belongsTo(TrainingModule::class, 'module_id');
    }

    /**
     * Paginated list of completions with optional filters.
     *
     * @param array<string, mixed> $filters
     * @return array<string, mixed>
     */
    public static function getAll(array $filters = []): array
    {
        $query = self::with(['module']);

        if (!empty($filters['module_id'])) {
            $query->where('module_id', (int) $filters['module_id']);
        }

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (isset($filters['certified'])) {
            $query->where('certified', (bool) $filters['certified']);
        }

        $perPage     = (int) ($filters['per_page'] ?? 20);
        $completions = $query->orderByDesc('completed_at')->paginate($perPage);

        return [
            'completions' => $completions->items(),
            'pagination'  => [
                'current_page' => $completions->currentPage(),
                'last_page'    => $completions->lastPage(),
                'per_page'     => $completions->perPage(),
                'total'        => $completions->total(),
            ],
        ];
    }

    /**
     * Record or update a training completion for a user.
     * Uses upsert so re-taking a module updates the existing record.
     */
    public static function record(int $moduleId, string $userId, ?int $score = null, bool $certified = false): self
    {
        return self::updateOrCreate(
            ['module_id' => $moduleId, 'user_id' => $userId],
            [
                'completed_at' => now(),
                'score'        => $score,
                'certified'    => $certified,
            ]
        );
    }
}
