<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ReplicatedStoreSettings extends Model
{
    protected $table = 'replicated_store_settings';

    protected $fillable = [
        'distributor_id',
        'reseller_id',
        'store_slug',
        'store_name',
        'banner_url',
        'welcome_message',
        'facebook_url',
        'instagram_url',
        'tiktok_url',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function distributor(): BelongsTo
    {
        return $this->belongsTo(DistributorProfile::class, 'distributor_id');
    }

    public function reseller(): BelongsTo
    {
        return $this->belongsTo(ResellerProfile::class, 'reseller_id');
    }

    public static function getAll(): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return self::with(['distributor', 'reseller'])
            ->orderByDesc('created_at')
            ->paginate(20);
    }

    public static function findBySlug(string $slug): ?self
    {
        return self::with(['distributor', 'reseller'])
            ->where('store_slug', $slug)
            ->first();
    }
}
