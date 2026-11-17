<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = ['number', 'source', 'order_id', 'subscription_id', 'template_id', 'issued_at', 'due_date', 'subtotal', 'tax_amount', 'total', 'status', 'pdf_path', 'file_path'];
    protected $casts = ['issued_at' => 'date', 'due_date' => 'date', 'subtotal' => 'integer', 'tax_amount' => 'integer', 'total' => 'integer'];
    public function order() { return $this->belongsTo(Order::class); }
    public function subscription() { return $this->belongsTo(Subscription::class); }
    public function payments() { return $this->hasMany(Payment::class); }
    public function paidAmount(): int { return (int) $this->payments()->sum('amount'); }
    public function template() { return $this->belongsTo(DocumentTemplate::class); }
}
