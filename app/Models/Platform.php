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
     * @param int|float $grossAmount
     * @return int
     */
    public function calculateFee(int|float $grossAmount): int
    {
        $commission = ($grossAmount * (float) $this->commission_percent) / 100;
        return (int) (round($commission) + $this->fixed_fee);
    }

    /**
     * Calculate net amount.
     * Formula: gross - platform_fee - other_fee
     *
     * @param int|float $grossAmount
     * @param int|float $otherFee
     * @param int|null $platformFeeOverride
     * @return int
     */
    public function calculateNet(int|float $grossAmount, int|float $otherFee = 0, ?int $platformFeeOverride = null): int
    {
        $fee = $platformFeeOverride !== null ? $platformFeeOverride : $this->calculateFee($grossAmount);
        return (int) round($grossAmount - $fee - $otherFee);
    }

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }
}
