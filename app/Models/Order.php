<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    protected $fillable = ['client_id', 'platform_id', 'service_package_id', 'title', 'external_order_ref', 'ordered_at', 'deadline', 'completed_at', 'status', 'revision_count', 'gross_amount', 'platform_fee', 'platform_fee_rule_id', 'platform_fee_override_reason', 'other_fee', 'other_fee_note', 'net_amount', 'payout_status', 'project_id', 'notes'];
    protected $casts = ['ordered_at' => 'date', 'deadline' => 'date', 'completed_at' => 'date', 'gross_amount' => 'integer', 'platform_fee' => 'integer', 'other_fee' => 'integer', 'net_amount' => 'integer'];

    public function client() { return $this->belongsTo(Client::class); }
    public function platform() { return $this->belongsTo(Platform::class); }
    public function servicePackage() { return $this->belongsTo(ServicePackage::class); }
    public function feeRule() { return $this->belongsTo(PlatformFeeRule::class, 'platform_fee_rule_id'); }
    public function invoices() { return $this->hasMany(Invoice::class); }
    public function withdrawals() { return $this->belongsToMany(Withdrawal::class); }
}
