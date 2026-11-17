<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Client;
use App\Models\Order;
use App\Models\Platform;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DataTableTest extends TestCase
{
    use RefreshDatabase;

    public function test_datatable_endpoints_return_json(): void
    {
        $user = User::factory()->create();

        $endpoints = [
            'datatable.orders', 'datatable.invoices', 'datatable.clients', 'datatable.payments',
            'datatable.agreements', 'datatable.subscriptions', 'datatable.projects',
            'datatable.platforms', 'datatable.expense-categories',
            'datatable.document-templates', 'datatable.reports',
        ];

        foreach ($endpoints as $name) {
            $this->actingAs($user)
                ->getJson(route($name, ['draw' => 1, 'start' => 0, 'length' => 10]))
                ->assertOk()
                ->assertJsonStructure(['draw', 'recordsTotal', 'recordsFiltered', 'data']);
        }
    }

    public function test_datatable_endpoints_support_ordering(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->getJson(route('datatable.orders', [
                'draw' => 1, 'start' => 0, 'length' => 10,
                'order' => [['column' => 0, 'dir' => 'asc']],
                'columns' => [['data' => 'title', 'name' => 'title', 'orderable' => 'true']],
            ]))
            ->assertOk();
    }

    public function test_datatable_index_pages_render(): void
    {
        $user = User::factory()->create();

        $pages = [
            'orders.index', 'invoices.index', 'clients.index', 'payments.index', 'agreements.index',
            'subscriptions.index', 'projects.index',
            'platforms.index', 'expense-categories.index', 'document-templates.index', 'reports.index',
        ];

        foreach ($pages as $name) {
            $this->actingAs($user)->get(route($name))->assertOk();
        }
    }

    public function test_fee_rule_page_renders_and_creates_rule(): void
    {
        $user = User::factory()->create();
        $platform = Platform::create(['name' => 'Fastwork', 'code' => 'fw-'.uniqid()]);

        $this->actingAs($user)->get(route('platforms.fee-rules', $platform))
            ->assertOk()
            ->assertSee('Tambah Tarif');

        $this->actingAs($user)->post(route('platforms.fee-rules.store', $platform), [
            'effective_from' => '2026-01-01',
            'components' => [
                ['type' => 'percent', 'value' => 5],
                ['type' => 'fixed', 'value' => 5000],
            ],
        ])->assertRedirect(route('platforms.fee-rules', $platform));

        $this->assertDatabaseCount('platform_fee_rules', 1);
        $this->assertDatabaseCount('platform_fee_components', 2);
    }

    public function test_order_uses_active_fee_rule_and_records_it(): void
    {
        $user = User::factory()->create();
        $platform = Platform::create(['name' => 'Fastwork', 'code' => 'fw-'.uniqid()]);
        $rule = \App\Models\PlatformFeeRule::create(['platform_id' => $platform->id, 'effective_from' => '2026-01-01']);
        \App\Models\PlatformFeeComponent::create(['platform_fee_rule_id' => $rule->id, 'type' => 'percent', 'value' => 10]);
        \App\Models\PlatformFeeComponent::create(['platform_fee_rule_id' => $rule->id, 'type' => 'max', 'value' => 25000]);
        $client = Client::create(['name' => 'Klien A']);

        $this->actingAs($user)->post(route('orders.store'), [
            'client_id' => $client->id,
            'platform_id' => $platform->id,
            'title' => 'Desain Logo',
            'ordered_at' => '2026-03-01',
            'status' => 'completed',
            'revision_count' => 0,
            'gross_amount' => 1000000,
        ])->assertRedirect(route('orders.show', Order::firstOrFail()));

        $order = Order::firstOrFail();
        $this->assertSame(25000, $order->platform_fee);
        $this->assertSame(975000, $order->net_amount);
        $this->assertSame($rule->id, $order->platform_fee_rule_id);
    }

    public function test_layout_defines_shared_datatable_language(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('clients.index'))
            ->assertOk()
            ->assertSee('const dtLanguage', false)
            ->assertSee('dataTables.bootstrap4.min.css', false);
    }

    public function test_datatable_endpoints_require_authentication(): void
    {
        $this->getJson(route('datatable.orders', ['draw' => 1, 'start' => 0, 'length' => 10]))
            ->assertUnauthorized();
    }
}
