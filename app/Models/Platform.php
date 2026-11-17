<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Platform extends Model
{
    protected $fillable = [
        'name',
        'code',
        'commission_percent',
        'fixed_fee',
        'withdrawal_fee',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'commission_percent' => 'decimal:2',
        'fixed_fee' => 'integer',
        'withdrawal_fee' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Calculate platform fee based on gross amount.
     * Formula: round(gross * commission_percent / 100) + fixed_fee
     *
     * @param int $grossAmount
     * @return int
     */
    public function calculateFee(int $grossAmount): int
    {
        $percentBasisPoints = (int) round(((float) $this->commission_percent) * 100);
        return intdiv(($grossAmount * $percentBasisPoints) + 5000, 10000) + $this->fixed_fee;
    }

    public function feeRules()
    {
        return $this->hasMany(PlatformFeeRule::class);
    }

    /**
     * Pakai tarif terbaru yang berlaku; jatuh ke formula lama kalau belum ada tarif.
     */
    public function feeRuleFor(string $date): ?PlatformFeeRule
    {
        return PlatformFeeRule::activeFor($this->id, $date);
    }

    public function feeFor(int $grossAmount, ?string $date = null): int
    {
        $rule = $this->feeRuleFor($date ?? now()->toDateString());

        return $rule ? $rule->calculateFee($grossAmount) : $this->calculateFee($grossAmount);
    }

    /**
     * Calculate net amount.
     * Formula: gross - platform_fee - other_fee
     *
     * @param int $grossAmount
     * @param int $otherFee
     * @param int|null $platformFeeOverride
     * @return int
     */
    public function calculateNet(int $grossAmount, int $otherFee = 0, ?int $platformFeeOverride = null): int
    {
        $fee = $platformFeeOverride !== null ? $platformFeeOverride : $this->calculateFee($grossAmount);
        return $grossAmount - $fee - $otherFee;
    }

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }
}
