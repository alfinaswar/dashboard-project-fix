<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Order;
use App\Models\Platform;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_order_page_renders(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('orders.create'))->assertOk();
    }

    public function test_store_order_works(): void
    {
        $user = User::factory()->create();
        $client = Client::create(['name' => 'Klien A', 'platform_id' => null, 'contact_name' => 'X', 'email' => 'a@b.c']);
        $platform = Platform::create(['name' => 'Fastwork', 'code' => 'fw1', 'commission_percent' => 5, 'fixed_fee' => 0]);

        $this->actingAs($user)->post(route('orders.store'), [
            'client_id' => $client->id,
            'platform_id' => $platform->id,
            'title' => 'Desain Logo',
            'ordered_at' => '2026-03-01',
            'status' => 'completed',
            'revision_count' => 0,
            'gross_amount' => 1000000,
        ])->assertRedirect();

        $order = Order::firstOrFail();
        $this->assertSame(50000, (int) $order->platform_fee);
        $this->assertCount(1, $order->feeItems);

        $this->actingAs($user)->get(route('orders.show', $order))->assertOk();
        $this->actingAs($user)->get(route('orders.edit', $order))->assertOk();
    }
}
