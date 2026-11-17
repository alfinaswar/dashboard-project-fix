<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServicePackage extends Model
{
    protected $fillable = ['name', 'description', 'billing_type', 'default_price', 'default_duration_months', 'is_active'];

    public const BILLING_TYPES = [
        'one_time' => 'Sekali beli',
        'subscription' => 'Langganan',
    ];

    protected $casts = [
        'default_price' => 'integer',
        'default_duration_months' => 'integer',
        'is_active' => 'boolean',
    ];

    public function getFormattedPriceAttribute(): string
    {
        return format_rupiah($this->default_price);
    }

    public function getBillingTypeLabelAttribute(): string
    {
        return self::BILLING_TYPES[$this->billing_type] ?? self::BILLING_TYPES['one_time'];
    }
}
