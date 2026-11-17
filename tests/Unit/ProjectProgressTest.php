<?php

namespace Tests\Unit;

use App\Models\Project;
use PHPUnit\Framework\TestCase;

class ProjectProgressTest extends TestCase
{
    public function test_project_progress_is_weighted_by_stage_progress(): void
    {
        $project = new Project();
        $project->setRelation('stages', collect([
            (object) ['weight' => 40, 'progress_percent' => 100],
            (object) ['weight' => 60, 'progress_percent' => 50],
        ]));

        $this->assertSame(70.0, $project->progress_percent);
    }
}
