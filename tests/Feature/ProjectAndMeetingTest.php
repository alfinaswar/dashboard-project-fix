<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Meeting;
use App\Models\Order;
use App\Models\Platform;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectAndMeetingTest extends TestCase
{
    use RefreshDatabase;

    private function createOrder(): Order
    {
        $platform = Platform::create([
            'name' => 'Fastwork',
            'code' => 'fastwork',
            'commission_percent' => 20.00,
            'fixed_fee' => 0,
            'is_active' => true,
        ]);
        $client = Client::create(['name' => 'Klien Tech', 'email' => 'tech@client.com']);

        return Order::create([
            'client_id' => $client->id,
            'platform_id' => $platform->id,
            'title' => 'Pengembangan Sistem ERP',
            'ordered_at' => '2026-02-01',
            'gross_amount' => 5000000,
            'platform_fee' => 1000000,
            'net_amount' => 4000000,
            'status' => 'confirmed',
        ]);
    }

    public function test_project_store_creates_per_order(): void
    {
        $user = User::factory()->create();
        $order = $this->createOrder();

        $response = $this->actingAs($user)->post(route('projects.store'), [
            'order_id' => $order->id,
            'name' => 'Proyek ERP Toko',
            'status' => 'running',
            'stages' => [
                ['name' => 'Desain Database', 'weight' => 50, 'progress_percent' => 100, 'status' => 'done'],
                ['name' => 'Koding Frontend', 'weight' => 50, 'progress_percent' => 50, 'status' => 'doing'],
            ],
        ]);

        $project = Project::firstOrFail();
        $response->assertRedirect(route('projects.show', $project));

        $this->assertSame($order->id, $project->order_id);
        $this->assertSame($order->client_id, $project->client_id);
        $this->assertSame(2, $project->stages()->count());
        $this->assertSame(75.0, $project->progress_percent);
    }

    public function test_project_kanban_stage_status_update_ajax(): void
    {
        $user = User::factory()->create();
        $order = $this->createOrder();

        $project = Project::create([
            'order_id' => $order->id,
            'client_id' => $order->client_id,
            'name' => 'Proyek Kanban Test',
            'status' => 'planning',
        ]);

        $stage = $project->stages()->create([
            'name' => 'Testing API',
            'weight' => 100,
            'progress_percent' => 0,
            'status' => 'todo',
        ]);

        // Pindahkan ke 'doing'
        $res = $this->actingAs($user)->postJson(route('projects.stages.update-status', [$project, $stage]), [
            'status' => 'doing',
        ]);
        $res->assertOk()->assertJson(['success' => true]);
        $this->assertSame('doing', $stage->fresh()->status);
        $this->assertSame(25, $stage->fresh()->progress_percent);

        // Pindahkan ke 'done'
        $resDone = $this->actingAs($user)->postJson(route('projects.stages.update-status', [$project, $stage]), [
            'status' => 'done',
        ]);
        $resDone->assertOk();
        $this->assertSame('done', $stage->fresh()->status);
        $this->assertSame(100, $stage->fresh()->progress_percent);
        $this->assertSame(100.0, $project->fresh()->progress_percent);
    }

    public function test_meeting_sticky_note_crud_and_status_move(): void
    {
        $user = User::factory()->create();
        $order = $this->createOrder();

        // 1. Buat meeting sticky note baru
        $res = $this->actingAs($user)->post(route('meetings.store'), [
            'title' => 'Sprint Planning 1',
            'order_id' => $order->id,
            'color' => 'yellow',
            'status' => 'scheduled',
            'notes' => 'Membahas alur autentikasi dan database',
            'action_items' => 'Buat migrasi table user',
        ]);
        $res->assertRedirect();

        $meeting = Meeting::firstOrFail();
        $this->assertSame('Sprint Planning 1', $meeting->title);
        $this->assertSame('yellow', $meeting->color);
        $this->assertSame('scheduled', $meeting->status);

        // 2. Pindahkan kolom kanban sticky note via Ajax
        $resMove = $this->actingAs($user)->postJson(route('meetings.update-status', $meeting), [
            'status' => 'action_needed',
            'color' => 'pink',
        ]);
        $resMove->assertOk()->assertJson(['success' => true]);

        $this->assertSame('action_needed', $meeting->fresh()->status);
        $this->assertSame('pink', $meeting->fresh()->color);

        // 3. Render meeting kanban board
        $this->actingAs($user)->get(route('meetings.index'))->assertOk();
    }
}
