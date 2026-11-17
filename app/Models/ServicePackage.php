<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServicePackage extends Model
{
    protected $fillable = [
        'name',
        'description',
        'default_price',
        'default_duration_months',
        'is_active',
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
}
