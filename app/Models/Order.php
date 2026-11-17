<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'client_id', 'platform_id', 'billing_type', 'title',
        'external_order_ref', 'ordered_at', 'deadline', 'completed_at',
        'status', 'revision_count', 'gross_amount', 'platform_fee',
        'platform_fee_rule_id', 'platform_fee_override_reason', 'other_fee', 'other_fee_note',
        'net_amount', 'project_id', 'notes',
    ];

    protected $casts = [
        'billing_type' => 'string',
        'ordered_at' => 'date',
        'deadline' => 'date',
        'completed_at' => 'date',
        'gross_amount' => 'integer',
        'platform_fee' => 'integer',
        'other_fee' => 'integer',
        'net_amount' => 'integer',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function platform()
    {
        return $this->belongsTo(Platform::class);
    }

    public function feeRule()
    {
        return $this->belongsTo(PlatformFeeRule::class, 'platform_fee_rule_id');
    }

    public function feeItems()
    {
        return $this->hasMany(OrderFeeItem::class)->orderBy('position')->orderBy('id');
    }

    public function getFeeItemsTotalAttribute(): int
    {
        $gross = (int) $this->gross_amount;

        return (int) $this->feeItems->sum(fn (OrderFeeItem $item) => $item->subtotalFor($gross));
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    /** Invoice order utama; invoice per pembayaran tidak dihitung sebagai tagihan. */
    public function orderInvoices()
    {
        return $this->invoices()->whereNull('payment_id');
    }

    public function agreements()
    {
        return $this->hasMany(Agreement::class);
    }

    /** Status pembayaran dari invoice order: belum ada invoice dianggap belum dibayar. */
    public function getPaymentStatusAttribute(): string
    {
        $invoices = ($this->relationLoaded('invoices') ? $this->invoices : $this->invoices()->get())
            ->whereNull('payment_id');

        if ($invoices->isEmpty()) {
            return 'unpaid';
        }

        return $invoices->every(fn (Invoice $invoice) => $invoice->status === 'paid') ? 'paid' : 'unpaid';
    }
}
