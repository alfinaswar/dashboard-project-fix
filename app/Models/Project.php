<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use SoftDeletes;

    public const STAGE_STATUSES = [
        'todo' => [
            'label' => 'Belum Dimulai',
            'badge' => 'secondary',
            'icon' => 'fas fa-hourglass-start',
        ],
        'doing' => [
            'label' => 'Sedang Dikerjakan',
            'badge' => 'primary',
            'icon' => 'fas fa-spinner fa-spin',
        ],
        'review' => [
            'label' => 'Revisi / Review',
            'badge' => 'warning',
            'icon' => 'fas fa-clipboard-check',
        ],
        'done' => [
            'label' => 'Selesai',
            'badge' => 'success',
            'icon' => 'fas fa-check-circle',
        ],
    ];

    protected $fillable = [
        'order_id',
        'client_id',
        'name',
        'description',
        'start_date',
        'target_date',
        'status',
    ];

    protected $casts = [
        'order_id' => 'integer',
        'start_date' => 'date',
        'target_date' => 'date',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function stages()
    {
        return $this->hasMany(ProjectStage::class)->orderBy('position');
    }

    public function meetings()
    {
        return $this->hasMany(Meeting::class)->orderByDesc('meeting_date');
    }

    public function getProgressPercentAttribute(): float
    {
        $stages = $this->relationLoaded('stages') ? $this->stages : $this->stages()->get();
        if ($stages->isEmpty()) {
            return 0.0;
        }

        $totalWeight = (int) $stages->sum('weight');
        if ($totalWeight > 0) {
            $sum = $stages->sum(fn ($stage) => ((int) $stage->weight * (int) $stage->progress_percent));
            return round($sum / $totalWeight, 1);
        }

        return round($stages->avg('progress_percent') ?? 0, 1);
    }
}
