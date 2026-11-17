<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = ['invoice_id', 'paid_at', 'amount', 'method', 'reference_no', 'proof_path'];
    protected $casts = ['paid_at' => 'date', 'amount' => 'integer'];
    public function invoice() { return $this->belongsTo(Invoice::class); }
    public function paymentInvoice() { return $this->hasOne(Invoice::class, 'payment_id'); }
}
