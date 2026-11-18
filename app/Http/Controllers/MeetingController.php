<?php

namespace App\Http\Controllers;

use App\Models\Meeting;
use App\Models\Order;
use App\Models\Project;
use Illuminate\Http\Request;

class MeetingController extends Controller
{
    public function index(Request $request)
    {
        $selectedOrderId = $request->query('order_id');
        $selectedProjectId = $request->query('project_id');
        $search = $request->query('search');

        $query = Meeting::with(['order.client', 'project']);

        if ($selectedOrderId) {
            $query->where('order_id', $selectedOrderId);
        }

        if ($selectedProjectId) {
            $query->where('project_id', $selectedProjectId);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
                  ->orWhere('attendees', 'like', "%{$search}%")
                  ->orWhere('action_items', 'like', "%{$search}%");
            });
        }

        $allMeetings = $query->orderBy('position')->orderByDesc('meeting_date')->get();

        // Group into Kanban Columns
        $kanbanColumns = [
            'scheduled' => [
                'meta' => Meeting::STATUSES['scheduled'],
                'items' => $allMeetings->where('status', 'scheduled')->values(),
            ],
            'in_progress' => [
                'meta' => Meeting::STATUSES['in_progress'],
                'items' => $allMeetings->where('status', 'in_progress')->values(),
            ],
            'action_needed' => [
                'meta' => Meeting::STATUSES['action_needed'],
                'items' => $allMeetings->where('status', 'action_needed')->values(),
            ],
            'done' => [
                'meta' => Meeting::STATUSES['done'],
                'items' => $allMeetings->where('status', 'done')->values(),
            ],
        ];

        $orders = Order::with('client')->latest('ordered_at')->get();
        $projects = Project::with('client')->latest()->get();

        return view('meetings.index', compact(
            'kanbanColumns',
            'orders',
            'projects',
            'selectedOrderId',
            'selectedProjectId',
            'search'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'order_id' => ['nullable', 'exists:orders,id'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'meeting_date' => ['nullable', 'date'],
            'attendees' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'action_items' => ['nullable', 'string'],
            'color' => ['required', 'in:yellow,green,pink,blue,purple,orange'],
            'status' => ['required', 'in:scheduled,in_progress,action_needed,done'],
        ], [
            'title.required' => 'Judul meeting wajib diisi.',
            'color.required' => 'Warna sticky note wajib dipilih.',
            'status.required' => 'Kolom status wajib dipilih.',
        ]);

        // Auto link project from order if empty
        if (!empty($validated['order_id']) && empty($validated['project_id'])) {
            $order = Order::with('project')->find($validated['order_id']);
            if ($order && $order->project) {
                $validated['project_id'] = $order->project->id;
            }
        }

        $position = Meeting::where('status', $validated['status'])->count();
        $meeting = Meeting::create([
            ...$validated,
            'position' => $position,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Catatan meeting berhasil ditambahkan.',
                'meeting' => $meeting->load(['order.client', 'project']),
            ]);
        }

        return back()->with('success', 'Catatan meeting berhasil ditambahkan.');
    }

    public function update(Request $request, Meeting $meeting)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'order_id' => ['nullable', 'exists:orders,id'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'meeting_date' => ['nullable', 'date'],
            'attendees' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'action_items' => ['nullable', 'string'],
            'color' => ['required', 'in:yellow,green,pink,blue,purple,orange'],
            'status' => ['required', 'in:scheduled,in_progress,action_needed,done'],
        ], [
            'title.required' => 'Judul meeting wajib diisi.',
        ]);

        $meeting->update($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Catatan meeting berhasil diperbarui.',
                'meeting' => $meeting->fresh(['order.client', 'project']),
            ]);
        }

        return back()->with('success', 'Catatan meeting berhasil diperbarui.');
    }

    /**
     * Ajax: Move sticky note between Kanban columns or change color.
     */
    public function updateStatus(Request $request, Meeting $meeting)
    {
        $validated = $request->validate([
            'status' => ['nullable', 'in:scheduled,in_progress,action_needed,done'],
            'color' => ['nullable', 'in:yellow,green,pink,blue,purple,orange'],
        ]);

        $meeting->update(array_filter($validated));

        return response()->json([
            'success' => true,
            'message' => "Sticky note '{$meeting->title}' berhasil diperbarui.",
            'meeting' => $meeting->fresh(),
        ]);
    }

    public function destroy(Request $request, Meeting $meeting)
    {
        $meeting->delete();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Catatan meeting berhasil dihapus.',
            ]);
        }

        return back()->with('success', 'Catatan meeting berhasil dihapus.');
    }
}
