<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Withdrawal extends Model
{
    protected $fillable = ['platform_id', 'withdrawn_at', 'amount_received', 'fee', 'bank_name', 'reference_no', 'notes'];
    protected $casts = ['withdrawn_at' => 'date', 'amount_received' => 'integer', 'fee' => 'integer'];
    public function platform() { return $this->belongsTo(Platform::class); }
    public function orders() { return $this->belongsToMany(Order::class); }
}
