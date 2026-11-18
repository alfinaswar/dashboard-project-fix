<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectStage extends Model
{
    protected $fillable = [
        'project_id',
        'name',
        'weight',
        'progress_percent',
        'status',
        'target_date',
        'actual_date',
        'position',
    ];

    protected $casts = [
        'target_date' => 'date',
        'actual_date' => 'date',
        'weight' => 'integer',
        'progress_percent' => 'integer',
        'position' => 'integer',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function getStatusMetaAttribute(): array
    {
        return Project::STAGE_STATUSES[$this->status] ?? [
            'label' => ucfirst($this->status),
            'badge' => 'secondary',
            'icon' => 'fas fa-tasks',
        ];
    }
}
