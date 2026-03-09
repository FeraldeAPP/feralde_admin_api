<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class DistributorRankHistory extends Model
{
    public $timestamps = false;

    protected $table = 'distributor_rank_history';

    protected $fillable = [
        'distributor_id',
        'previous_rank',
        'new_rank',
        'changed_at',
        'changed_by',
        'reason',
    ];

    protected $casts = [
        'changed_at' => 'datetime',
    ];

    public function distributor(): BelongsTo
    {
        return $this->belongsTo(DistributorProfile::class, 'distributor_id');
    }
}
