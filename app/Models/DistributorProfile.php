<?php

declare(strict_types=1);

namespace App\Models;

use App\Mail\DistributorWelcomeMail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

final class DistributorProfile extends Model
{
    protected $fillable = [
        'user_id',
        'distributor_code',
        'rank',
        'referral_code',
        'parent_distributor_id',
        'assigned_city',
        'application_doc_url',
        'approved_at',
        'approved_by',
        'rejected_at',
        'rejected_by',
        'rejection_reason',
        'suspended_at',
        'suspended_by',
        'suspension_reason',
        'total_network_sales',
        'total_personal_sales',
        'rank_updated_at',
        'bank_account_name',
        'bank_account_number',
        'bank_name',
        'e_wallet_gcash',
        'e_wallet_maya',
        'payment_confirmed_at',
        'payment_proof_path',
        'business_name',
        'business_type',
        'selected_city',
        'tin_or_reg_no',
        'business_address',
        'contact_number',
        'date_of_birth',
        'gender',
        'region',
        'province',
        'city',
        'barangay',
        'street_address',
        'zip_code',
        'landmark',
        'facebook_url',
        'tiktok_username',
        'onboarding_completed_at',
    ];

    protected $casts = [
        'total_network_sales'  => 'decimal:4',
        'total_personal_sales' => 'decimal:4',
        'approved_at'          => 'datetime',
        'rejected_at'          => 'datetime',
        'suspended_at'         => 'datetime',
        'rank_updated_at'      => 'datetime',
        'payment_confirmed_at' => 'datetime',
        'onboarding_completed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parentDistributor(): BelongsTo
    {
        return $this->belongsTo(DistributorProfile::class, 'parent_distributor_id');
    }

    public function childDistributors(): HasMany
    {
        return $this->hasMany(DistributorProfile::class, 'parent_distributor_id');
    }

    /**
     * Resellers directly linked to this distributor by invitation.
     */
    public function resellers(): HasMany
    {
        return $this->hasMany(ResellerProfile::class, 'parent_distributor_id');
    }

    public function rankHistory(): HasMany
    {
        return $this->hasMany(DistributorRankHistory::class, 'distributor_id');
    }

    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class, 'distributor_id');
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(Commission::class, 'distributor_id');
    }

    /**
     * All resellers in this distributor's network:
     * - Resellers directly invited by this distributor (any city)
     * - Resellers whose city matches this distributor's assigned_city
     */
    public function networkResellers(): Collection
    {
        return ResellerProfile::where(function (Builder $q): void {
            $q->where('parent_distributor_id', $this->id);

            if ($this->assigned_city !== null) {
                $q->orWhere('city', $this->assigned_city);
            }
        })->get();
    }

    /**
     * Find the distributor assigned to a specific city, if any.
     */
    public static function getForCity(string $city): ?self
    {
        return self::where('assigned_city', $city)->first();
    }

    public static function validate(array $data, ?int $excludeId = null): array
    {
        $codeRule = $excludeId
            ? "required|string|unique:distributor_profiles,distributor_code,{$excludeId}"
            : 'required|string|unique:distributor_profiles,distributor_code';

        $cityRule = $excludeId
            ? "nullable|string|max:150|unique:distributor_profiles,assigned_city,{$excludeId}"
            : 'nullable|string|max:150|unique:distributor_profiles,assigned_city';

        $validator = Validator::make($data, [
            'user_id'               => 'required|string',
            'distributor_code'      => $codeRule,
            'rank'                  => 'sometimes|string|in:STARTER,BRONZE,SILVER,GOLD,PLATINUM,DIAMOND',
            'referral_code'         => 'required|string|unique:distributor_profiles,referral_code' . ($excludeId ? ",{$excludeId}" : ''),
            'parent_distributor_id' => 'nullable|integer|exists:distributor_profiles,id',
            'assigned_city'         => $cityRule,
            'bank_account_name'     => 'nullable|string|max:200',
            'bank_account_number'   => 'nullable|string|max:50',
            'bank_name'             => 'nullable|string|max:100',
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

    /**
     * @param array<string, mixed> $filters
     * @return array<string, mixed>
     */
    public static function getAll(array $filters = []): array
    {
        $query = self::with(['wallet']);

        if (!empty($filters['rank'])) {
            $query->where('rank', $filters['rank']);
        }

        if (!empty($filters['city'])) {
            $query->where('assigned_city', $filters['city']);
        }

        if (isset($filters['has_city'])) {
            if ($filters['has_city']) {
                $query->whereNotNull('assigned_city');
            } else {
                $query->whereNull('assigned_city');
            }
        }

        if (!empty($filters['status'])) {
            $query = match ($filters['status']) {
                'pending'   => $query->whereNull('approved_at')->whereNull('rejected_at')->whereNull('suspended_at'),
                'approved'  => $query->whereNotNull('approved_at')->whereNull('suspended_at'),
                'rejected'  => $query->whereNotNull('rejected_at'),
                'suspended' => $query->whereNotNull('suspended_at'),
                default     => $query,
            };
        }

        if (!empty($filters['search'])) {
            $query->where(function (Builder $q) use ($filters): void {
                $q->where('distributor_code', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('referral_code', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('assigned_city', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('user_id', $filters['search']);
            });
        }

        $perPage      = (int) ($filters['per_page'] ?? 20);
        $distributors = $query->orderByDesc('created_at')->paginate($perPage);

        return [
            'distributors' => $distributors->items(),
            'pagination'   => [
                'current_page' => $distributors->currentPage(),
                'last_page'    => $distributors->lastPage(),
                'per_page'     => $distributors->perPage(),
                'total'        => $distributors->total(),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function getPending(array $filters = []): array
    {
        $filters['status'] = 'pending';
        return self::getAll($filters);
    }

    public function approve(string $approvedBy): bool
    {
        return DB::transaction(function () use ($approvedBy): bool {
            $result = $this->update([
                'approved_at'   => now(),
                'approved_by'   => $approvedBy,
                'rejected_at'   => null,
                'rejected_by'   => null,
                'suspended_at'  => null,
            ]);

            Wallet::firstOrCreate(['distributor_id' => $this->id]);

            return $result;
        });
    }

    public function reject(string $rejectedBy, ?string $reason = null): bool
    {
        return $this->update([
            'rejected_at'      => now(),
            'rejected_by'      => $rejectedBy,
            'rejection_reason' => $reason,
            'approved_at'      => null,
        ]);
    }

    public function suspend(string $suspendedBy, ?string $reason = null): bool
    {
        return $this->update([
            'suspended_at'      => now(),
            'suspended_by'      => $suspendedBy,
            'suspension_reason' => $reason,
        ]);
    }

    public function unsuspend(): bool
    {
        return $this->update([
            'suspended_at'      => null,
            'suspended_by'      => null,
            'suspension_reason' => null,
        ]);
    }

    /**
     * Assign this distributor as the exclusive distributor for a Philippine city.
     * Fails if another distributor already holds that city.
     */
    public function assignCity(string $city): bool
    {
        return DB::transaction(function () use ($city): bool {
            $conflict = self::where('assigned_city', $city)
                ->where('id', '!=', $this->id)
                ->first();

            if ($conflict !== null) {
                throw new HttpResponseException(response()->json([
                    'success' => false,
                    'message' => "City '{$city}' is already assigned to distributor {$conflict->distributor_code}",
                ], 422));
            }

            return $this->update(['assigned_city' => $city]);
        });
    }

    /**
     * Remove this distributor's city assignment.
     * Resellers previously covered by city become direct-ordering resellers.
     */
    public function unassignCity(): bool
    {
        return $this->update(['assigned_city' => null]);
    }

    public function updateRank(string $newRank, ?string $changedBy = null, ?string $reason = null): bool
    {
        if ($this->rank === $newRank) {
            return false;
        }

        return DB::transaction(function () use ($newRank, $changedBy, $reason): bool {
            DistributorRankHistory::create([
                'distributor_id' => $this->id,
                'previous_rank'  => $this->rank,
                'new_rank'       => $newRank,
                'changed_by'     => $changedBy,
                'reason'         => $reason,
            ]);

            return $this->update([
                'rank'            => $newRank,
                'rank_updated_at' => now(),
            ]);
        });
    }
    
    public function confirmPayment(): bool
    {
        $updated = $this->update([
            'payment_confirmed_at' => now(),
        ]);

        if ($updated) {
            $this->loadMissing('user');
            
            if ($this->user) {
                Mail::to($this->user->email)->send(new DistributorWelcomeMail($this->user->name));
            }
        }

        return $updated;
    }
}
