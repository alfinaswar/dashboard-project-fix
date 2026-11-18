<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Meeting extends Model
{
    use SoftDeletes;

    public const COLORS = [
        'yellow' => [
            'name' => 'Kuning Pastel',
            'bg' => '#FEF08A',
            'border' => '#FACC15',
            'text' => '#713F12',
            'pin' => '#CA8A04',
        ],
        'green' => [
            'name' => 'Hijau Mint',
            'bg' => '#BBF7D0',
            'border' => '#86EFAC',
            'text' => '#14532D',
            'pin' => '#16A34A',
        ],
        'pink' => [
            'name' => 'Merah Muda',
            'bg' => '#FECDD3',
            'border' => '#FDA4AF',
            'text' => '#881337',
            'pin' => '#E11D48',
        ],
        'blue' => [
            'name' => 'Biru Langit',
            'bg' => '#BAE6FD',
            'border' => '#7DD3FC',
            'text' => '#0C4A6E',
            'pin' => '#0284C7',
        ],
        'purple' => [
            'name' => 'Ungu Lavender',
            'bg' => '#E9D5FF',
            'border' => '#D8B4FE',
            'text' => '#581C87',
            'pin' => '#9333EA',
        ],
        'orange' => [
            'name' => 'Jingga Pastel',
            'bg' => '#FED7AA',
            'border' => '#FDBA74',
            'text' => '#7C2D12',
            'pin' => '#EA580C',
        ],
    ];

    public const STATUSES = [
        'scheduled' => [
            'label' => 'Terjadwal',
            'icon' => 'fas fa-calendar-alt',
            'badge' => 'primary',
        ],
        'in_progress' => [
            'label' => 'Sedang Dibahas',
            'icon' => 'fas fa-comments',
            'badge' => 'info',
        ],
        'action_needed' => [
            'label' => 'Tindak Lanjut',
            'icon' => 'fas fa-bolt',
            'badge' => 'warning',
        ],
        'done' => [
            'label' => 'Selesai & Arsip',
            'icon' => 'fas fa-check-circle',
            'badge' => 'success',
        ],
    ];

    protected $fillable = [
        'order_id',
        'project_id',
        'title',
        'meeting_date',
        'attendees',
        'notes',
        'action_items',
        'color',
        'status',
        'position',
    ];

    protected $casts = [
        'meeting_date' => 'datetime',
        'position' => 'integer',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function getColorMetaAttribute(): array
    {
        return self::COLORS[$this->color] ?? self::COLORS['yellow'];
    }

    public function getStatusMetaAttribute(): array
    {
        return self::STATUSES[$this->status] ?? self::STATUSES['scheduled'];
    }
}
