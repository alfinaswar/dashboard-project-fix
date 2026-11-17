<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Client extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'platform_id',
        'name',
        'company_name',
        'contact_name',
        'email',
        'phone',
        'address',
        'npwp',
        'external_ref',
        'notes',
    ];

    public function platform(): BelongsTo
    {
        return $this->belongsTo(Platform::class);
    }
}
