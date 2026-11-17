<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlatformFeeComponent extends Model
{
    public const TYPES = [
        'percent' => 'Persen dari nilai kotor (%)',
        'fixed' => 'Nominal tetap per order (Rp)',
        'min' => 'Batas minimal potongan (Rp)',
        'max' => 'Batas maksimal potongan (Rp)',
    ];

    protected $fillable = ['platform_fee_rule_id', 'type', 'value', 'position', 'note'];

    protected $casts = ['value' => 'decimal:2', 'position' => 'integer'];

    public function rule(): BelongsTo
    {
        return $this->belongsTo(PlatformFeeRule::class, 'platform_fee_rule_id');
    }
}
