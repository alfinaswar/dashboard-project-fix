<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'client_id', 'platform_id', 'billing_type', 'title',
        'external_order_ref', 'ordered_at', 'deadline', 'completed_at',
        'status', 'revision_count', 'gross_amount', 'platform_fee',
        'platform_fee_override_reason', 'other_fee', 'other_fee_note',
        'net_amount', 'project_id', 'notes',
    ];

    protected $casts = [
        'billing_type' => 'string',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function platform()
    {
        return $this->belongsTo(Platform::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}