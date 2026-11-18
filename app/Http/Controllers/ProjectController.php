<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\Order;
use App\Models\Project;
use App\Models\ProjectStage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $viewMode = $request->query('view', 'kanban'); // 'kanban' or 'table'
        $projects = Project::with(['order.client', 'order.platform', 'client', 'stages'])
            ->latest()
            ->paginate(15);

        // Group projects by status for Kanban view
        $kanbanProjects = [
            'planning' => Project::with(['order.client', 'stages', 'meetings'])->where('status', 'planning')->latest()->get(),
            'running' => Project::with(['order.client', 'stages', 'meetings'])->where('status', 'running')->latest()->get(),
            'on_hold' => Project::with(['order.client', 'stages', 'meetings'])->where('status', 'on_hold')->latest()->get(),
            'done' => Project::with(['order.client', 'stages', 'meetings'])->where('status', 'done')->latest()->get(),
        ];

        return view('projects.index', compact('projects', 'kanbanProjects', 'viewMode'));
    }

    public function create()
    {
        $orders = Order::with(['client', 'platform'])->latest('ordered_at')->get();
        $defaultStages = [
            ['name' => 'Analisis & Kebutuhan', 'weight' => 10, 'progress_percent' => 0, 'status' => 'todo'],
            ['name' => 'Desain UI / Prototipe', 'weight' => 15, 'progress_percent' => 0, 'status' => 'todo'],
            ['name' => 'Development & Koding', 'weight' => 40, 'progress_percent' => 0, 'status' => 'todo'],
            ['name' => 'Testing, Bugfix & QA', 'weight' => 20, 'progress_percent' => 0, 'status' => 'todo'],
            ['name' => 'Deployment & Serah Terima', 'weight' => 15, 'progress_percent' => 0, 'status' => 'todo'],
        ];

        return view('projects.form', [
            'project' => new Project(['status' => 'planning']),
            'orders' => $orders,
            'clients' => Client::orderBy('name')->get(),
            'defaultStages' => $defaultStages,
        ]);
    }

    public function store(StoreProjectRequest $request)
    {
        $data = $request->validated();
        $stages = $data['stages'] ?? [];
        unset($data['stages']);

        $order = Order::find($data['order_id'] ?? null);
        if ($order) {
            $data['client_id'] = $order->client_id;
        }

        $project = DB::transaction(function () use ($data, $stages) {
            $project = Project::create($data);
            foreach ($stages as $position => $stage) {
                $project->stages()->create([...$stage, 'position' => $position]);
            }
            $this->log($project, 'created', $data);

            return $project;
        });

        return redirect()->route('projects.show', $project)->with('success', 'Proyek berhasil dibuat.');
    }

    public function show(Project $project)
    {
        $project->load(['order.client', 'order.platform', 'client', 'stages', 'meetings.order']);

        // Group stages by Kanban columns
        $stagesByColumn = [
            'todo' => $project->stages->where('status', 'todo')->values(),
            'doing' => $project->stages->where('status', 'doing')->values(),
            'review' => $project->stages->where('status', 'review')->values(),
            'done' => $project->stages->where('status', 'done')->values(),
        ];

        return view('projects.show', compact('project', 'stagesByColumn'));
    }

    public function edit(Project $project)
    {
        $project->load(['order.client', 'stages']);
        $orders = Order::with(['client', 'platform'])->latest('ordered_at')->get();

        return view('projects.form', [
            'project' => $project,
            'orders' => $orders,
            'clients' => Client::orderBy('name')->get(),
            'defaultStages' => $project->stages,
        ]);
    }

    public function update(StoreProjectRequest $request, Project $project)
    {
        $data = $request->validated();
        $stages = $data['stages'] ?? [];
        unset($data['stages']);

        $order = Order::find($data['order_id'] ?? null);
        if ($order) {
            $data['client_id'] = $order->client_id;
        }

        DB::transaction(function () use ($data, $stages, $project) {
            $before = $project->only(['name', 'status', 'target_date', 'order_id']);
            $project->update($data);
            $project->stages()->delete();
            foreach ($stages as $position => $stage) {
                $project->stages()->create([...$stage, 'position' => $position]);
            }
            $this->log($project, 'updated', ['before' => $before, 'after' => $data]);
        });

        return redirect()->route('projects.show', $project)->with('success', 'Proyek berhasil diperbarui.');
    }

    public function destroy(Project $project)
    {
        $project->delete();
        $this->log($project, 'deleted');

        return redirect()->route('projects.index')->with('success', 'Proyek berhasil dihapus.');
    }

    /**
     * Ajax: Update stage status / progress on the Kanban board.
     */
    public function updateStageStatus(Request $request, Project $project, ProjectStage $stage)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:todo,doing,review,done'],
            'progress_percent' => ['nullable', 'integer', 'min:0', 'max:100'],
        ]);

        $status = $validated['status'];
        $progress = $validated['progress_percent'] ?? null;

        if (is_null($progress)) {
            if ($status === 'done') {
                $progress = 100;
            } elseif ($status === 'todo') {
                $progress = 0;
            } elseif ($status === 'doing' && $stage->progress_percent === 0) {
                $progress = 25;
            } else {
                $progress = $stage->progress_percent;
            }
        }

        $updateData = [
            'status' => $status,
            'progress_percent' => $progress,
        ];

        if ($status === 'done' && ! $stage->actual_date) {
            $updateData['actual_date'] = now()->toDateString();
        }

        $stage->update($updateData);

        // Recalculate project overall status if all done
        $project->load('stages');
        $overallProgress = $project->progress_percent;
        if ($overallProgress >= 100 && $project->status !== 'done') {
            $project->update(['status' => 'done']);
        } elseif ($overallProgress > 0 && $project->status === 'planning') {
            $project->update(['status' => 'running']);
        }

        return response()->json([
            'success' => true,
            'message' => "Tahapan '{$stage->name}' berhasil diperbarui ke " . ucfirst($status),
            'stage' => $stage->fresh(),
            'project_progress' => $overallProgress,
            'project_status' => $project->fresh()->status,
        ]);
    }

    /**
     * Ajax: Tambah stage baru langsung dari Kanban.
     */
    public function storeStage(Request $request, Project $project)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'weight' => ['required', 'integer', 'min:1', 'max:100'],
            'status' => ['required', 'in:todo,doing,review,done'],
            'target_date' => ['nullable', 'date'],
        ]);

        $progress = match ($validated['status']) {
            'done' => 100,
            'doing' => 25,
            'review' => 75,
            default => 0,
        };

        $position = $project->stages()->count();
        $stage = $project->stages()->create([
            ...$validated,
            'progress_percent' => $progress,
            'position' => $position,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tahapan berhasil ditambahkan ke Kanban board.',
            'stage' => $stage,
            'project_progress' => $project->fresh()->progress_percent,
        ]);
    }

    /**
     * Ajax: Hapus stage dari Kanban.
     */
    public function destroyStage(Project $project, ProjectStage $stage)
    {
        $stage->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tahapan berhasil dihapus.',
            'project_progress' => $project->fresh()->progress_percent,
        ]);
    }

    private function log(Project $project, string $action, array $changes = []): void
    {
        ActivityLog::create([
            'user_id' => auth()->id(),
            'subject_type' => Project::class,
            'subject_id' => $project->id,
            'action' => $action,
            'changes' => $changes,
            'created_at' => now(),
        ]);
    }
}
