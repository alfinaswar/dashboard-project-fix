<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderFeeItem extends Model
{
    public const TYPES = ['amount' => 'Rp', 'percent' => '%'];

    protected $fillable = ['order_id', 'label', 'amount_type', 'amount', 'position'];

    protected $casts = ['amount' => 'integer', 'position' => 'integer'];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Nominal potongan riil untuk nilai kotor tertentu.
     */
    public function subtotalFor(int $grossAmount): int
    {
        if ($this->amount_type === 'percent') {
            return (int) round($grossAmount * (float) $this->amount / 100);
        }

        return (int) $this->amount;
    }
}
