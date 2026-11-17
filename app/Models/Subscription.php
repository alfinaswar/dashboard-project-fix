<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subscription extends Model
{
    use SoftDeletes;

    /** Lama masa aktif (bulan) untuk tiap jenis langganan; 0 = tanpa masa aktif. */
    public const DURATIONS = ['annual' => 12, 'monthly' => 1, 'one_time' => 0];

    protected $fillable = [
        'client_id',
        'billing_type',
        'previous_subscription_id',
        'start_date',
        'end_date',
        'contract_value',
        'status',
        'freeze_reason',
        'notes',
        'reminder_30_sent_at',
        'reminder_14_sent_at',
        'reminder_7_sent_at',
        'expired_notified_at',
    ];

    protected $casts = [
        'billing_type' => 'string',
        'start_date' => 'date',
        'end_date' => 'date',
        'contract_value' => 'integer',
        'reminder_30_sent_at' => 'datetime',
        'reminder_14_sent_at' => 'datetime',
        'reminder_7_sent_at' => 'datetime',
        'expired_notified_at' => 'datetime',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function previous()
    {
        return $this->belongsTo(self::class, 'previous_subscription_id');
    }

    public function next()
    {
        return $this->hasOne(self::class, 'previous_subscription_id');
    }

    /** Lama masa aktif dalam bulan untuk jenis langganan ini. */
    public function durationMonths(): int
    {
        return self::DURATIONS[$this->billing_type] ?? 12;
    }

    public function getCalculatedStatusAttribute(): string
    {
        if ($this->status === 'frozen' || $this->status === 'renewed') {
            return $this->status;
        }

        if (! $this->end_date) {
            return 'active';
        }

        if ($this->end_date->copy()->endOfDay()->isPast()) {
            return 'expired';
        }

        return $this->daysLeft() <= 30 ? 'expiring' : 'active';
    }

    /** Sisa hari masa aktif; 0 kalau sudah berakhir atau tidak ada tanggal berakhir. */
    public function daysLeft(): int
    {
        if (! $this->end_date) {
            return 0;
        }

        return max(0, (int) now()->startOfDay()->diffInDays($this->end_date->copy()->startOfDay(), false));
    }

    public function getBillingTypeLabelAttribute(): string
    {
        return ServicePackage::BILLING_TYPES[$this->billing_type] ?? '-';
    }

    public function renewalStartDate()
    {
        return $this->end_date ? $this->end_date->copy()->addDay() : now()->startOfDay();
    }
}