<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    protected $fillable = [
        'code', 'name', 'description',
        'price_monthly', 'price_half_yearly', 'price_yearly', 'price_extra_branch',
        'price_perpetual', 'price_amc',
        'branch_limit', 'trial_days', 'modules', 'features', 'is_active', 'is_public', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price_monthly' => 'decimal:2',
            'price_half_yearly' => 'decimal:2',
            'price_yearly' => 'decimal:2',
            'price_extra_branch' => 'decimal:2',
            'price_perpetual' => 'decimal:2',
            'price_amc' => 'decimal:2',
            'modules' => 'array',
            'features' => 'array',
            'is_active' => 'boolean',
            'is_public' => 'boolean',
        ];
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Recurring price for a billing cycle and branch count.
     * price_extra_branch is stored as a yearly figure and pro-rated to the cycle.
     */
    public function priceFor(string $cycle, int $branches = 1): float
    {
        $base = match ($cycle) {
            'monthly' => (float) $this->price_monthly,
            'half_yearly' => (float) $this->price_half_yearly,
            default => (float) $this->price_yearly,
        };

        $extraPerBranch = match ($cycle) {
            'monthly' => (float) $this->price_extra_branch / 12,
            'half_yearly' => (float) $this->price_extra_branch / 2,
            default => (float) $this->price_extra_branch,
        };

        return round($base + max(0, $branches - 1) * $extraPerBranch, 2);
    }

    public function hasFeature(string $feature): bool
    {
        return (bool) data_get($this->features, $feature, false);
    }

    public function moduleList(): ?array
    {
        return $this->modules ?: null; // null => all modules
    }
}
