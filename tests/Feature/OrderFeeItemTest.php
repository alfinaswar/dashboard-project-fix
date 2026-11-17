<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Order;
use App\Models\Platform;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderFeeItemTest extends TestCase
{
    use RefreshDatabase;

    private function basePayload(Client $client, Platform $platform, array $extra = []): array
    {
        return array_merge([
            'client_id' => $client->id,
            'platform_id' => $platform->id,
            'title' => 'Desain Logo',
            'ordered_at' => '2026-03-01',
            'status' => 'completed',
            'revision_count' => 0,
            'gross_amount' => 1000000,
        ], $extra);
    }

    public function test_multiple_fee_items_sum_into_platform_fee_and_net(): void
    {
        $user = User::factory()->create();
        $client = Client::create(['name' => 'Klien A']);
        $platform = Platform::create(['name' => 'Fastwork', 'code' => 'fw-'.uniqid()]);

        $this->actingAs($user)->post(route('orders.store'), $this->basePayload($client, $platform, [
            'other_fee' => 5000,
            'fee_items' => [
                ['label' => 'Komisi Fastwork', 'amount' => 50000],
                ['label' => 'Pajak', 'amount' => 10000],
                ['label' => 'Biaya admin', 'amount' => 2500],
            ],
        ]))->assertRedirect(route('orders.show', Order::firstOrFail()));

        $order = Order::with('feeItems')->firstOrFail();
        $this->assertSame(62500, $order->platform_fee);
        $this->assertSame(932500, $order->net_amount);
        $this->assertCount(3, $order->feeItems);
        $this->assertSame(62500, $order->fee_items_total);
        $this->assertSame('Komisi Fastwork', $order->feeItems->first()->label);
    }

    public function test_empty_fee_items_fall_back_to_platform_rule(): void
    {
        $user = User::factory()->create();
        $client = Client::create(['name' => 'Klien A']);
        $platform = Platform::create(['name' => 'Fastwork', 'code' => 'fw-'.uniqid(), 'commission_percent' => 5, 'fixed_fee' => 0]);

        $this->actingAs($user)->post(route('orders.store'), $this->basePayload($client, $platform))
            ->assertRedirect(route('orders.show', Order::firstOrFail()));

        $order = Order::with('feeItems')->firstOrFail();
        $this->assertSame(50000, $order->platform_fee);
        $this->assertCount(1, $order->feeItems);
        $this->assertSame(50000, $order->feeItems->first()->amount);
        $this->assertSame(950000, $order->net_amount);
    }

    public function test_updating_order_replaces_fee_items(): void
    {
        $user = User::factory()->create();
        $client = Client::create(['name' => 'Klien A']);
        $platform = Platform::create(['name' => 'Fastwork', 'code' => 'fw-'.uniqid()]);

        $this->actingAs($user)->post(route('orders.store'), $this->basePayload($client, $platform, [
            'fee_items' => [['label' => 'Komisi', 'amount' => 10000]],
        ]));
        $order = Order::firstOrFail();

        $this->actingAs($user)->put(route('orders.update', $order), $this->basePayload($client, $platform, [
            'fee_items' => [
                ['label' => 'Komisi baru', 'amount' => 20000],
                ['label' => 'Pajak', 'amount' => 3000],
            ],
        ]))->assertRedirect(route('orders.show', $order));

        $order->refresh()->load('feeItems');
        $this->assertSame(23000, $order->platform_fee);
        $this->assertCount(2, $order->feeItems);
        $this->assertSame('Komisi baru', $order->feeItems->first()->label);
    }

    public function test_fee_item_requires_label_and_amount(): void
    {
        $user = User::factory()->create();
        $client = Client::create(['name' => 'Klien A']);
        $platform = Platform::create(['name' => 'Fastwork', 'code' => 'fw-'.uniqid()]);

        $this->actingAs($user)->post(route('orders.store'), $this->basePayload($client, $platform, [
            'fee_items' => [['label' => '', 'amount' => '']],
        ]))->assertSessionHasErrors(['fee_items.0.label', 'fee_items.0.amount']);
    }

    public function test_order_form_renders_price_section(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('orders.create'))
            ->assertOk()
            ->assertSee('Informasi Order')
            ->assertSee('Rincian Potongan')
            ->assertSee('Nilai Bersih');
    }
}
