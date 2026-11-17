<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Order;
use App\Models\Platform;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderFeePercentTest extends TestCase
{
    use RefreshDatabase;

    private function payload(Client $client, Platform $platform, array $extra = []): array
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

    public function test_percent_and_amount_items_are_combined(): void
    {
        $user = User::factory()->create();
        $client = Client::create(['name' => 'Klien A']);
        $platform = Platform::create(['name' => 'Fastwork', 'code' => 'fw-'.uniqid()]);

        $this->actingAs($user)->post(route('orders.store'), $this->payload($client, $platform, [
            'fee_items' => [
                ['label' => 'Komisi Fastwork', 'amount_type' => 'percent', 'amount' => 5],
                ['label' => 'Pajak', 'amount_type' => 'amount', 'amount' => 10000],
            ],
        ]))->assertRedirect(route('orders.show', Order::firstOrFail()));

        $order = Order::with('feeItems')->firstOrFail();
        $this->assertSame(60000, $order->platform_fee);
        $this->assertSame(940000, $order->net_amount);
        $this->assertSame(60000, $order->fee_items_total);
        $this->assertSame('percent', $order->feeItems->first()->amount_type);
    }

    public function test_percent_item_follows_gross_amount(): void
    {
        $user = User::factory()->create();
        $client = Client::create(['name' => 'Klien A']);
        $platform = Platform::create(['name' => 'Fastwork', 'code' => 'fw-'.uniqid()]);

        $this->actingAs($user)->post(route('orders.store'), $this->payload($client, $platform, [
            'gross_amount' => 2000000,
            'fee_items' => [['label' => 'Komisi', 'amount_type' => 'percent', 'amount' => 10]],
        ]));

        $order = Order::with('feeItems')->firstOrFail();
        $this->assertSame(200000, $order->platform_fee);
        $this->assertSame(200000, $order->fee_items_total);
    }

    public function test_invalid_amount_type_is_rejected(): void
    {
        $user = User::factory()->create();
        $client = Client::create(['name' => 'Klien A']);
        $platform = Platform::create(['name' => 'Fastwork', 'code' => 'fw-'.uniqid()]);

        $this->actingAs($user)->post(route('orders.store'), $this->payload($client, $platform, [
            'fee_items' => [['label' => 'Komisi', 'amount_type' => 'rupiah', 'amount' => 10]],
        ]))->assertSessionHasErrors(['fee_items.0.amount_type']);
    }

    public function test_order_form_has_rupiah_inputs_and_type_selector(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('orders.create'))
            ->assertOk()
            ->assertSee('rupiah-input')
            ->assertSee('amount_type')
            ->assertSee('fee-suffix');
    }

    public function test_layout_masks_rupiah_inputs(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('clients.index'))
            ->assertOk()
            ->assertSee('maskRupiah')
            ->assertSee('rupiah-input');
    }
}
