<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Order;
use App\Models\Platform;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionTest extends TestCase
{
    use RefreshDatabase;

    private function createOrder(array $attributes = []): Order
    {
        $platform = Platform::create([
            'name' => 'Fastwork',
            'code' => 'fastwork',
            'commission_percent' => 20.00,
            'fixed_fee' => 0,
            'is_active' => true,
        ]);
        $client = Client::create(['name' => 'Klien A', 'contact_name' => 'X', 'email' => 'a@b.c']);

        return Order::create(array_merge([
            'client_id' => $client->id,
            'platform_id' => $platform->id,
            'title' => 'Pembuatan Web SaaS',
            'ordered_at' => '2026-01-01',
            'gross_amount' => 1500000,
            'platform_fee' => 300000,
            'net_amount' => 1200000,
            'status' => 'completed',
        ], $attributes));
    }

    public function test_index_renders(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get(route('subscriptions.index'))->assertOk();
    }

    public function test_create_renders(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get(route('subscriptions.create'))->assertOk();
    }

    public function test_store_creates_per_order(): void
    {
        $user = User::factory()->create();
        $order = $this->createOrder();

        $this->actingAs($user)->post(route('subscriptions.store'), [
            'order_id' => $order->id,
            'client_id' => $order->client_id,
            'billing_type' => 'annual',
            'start_date' => '2026-01-01',
            'contract_value' => 1500000,
            'status' => 'active',
        ])->assertRedirect(route('subscriptions.index'));

        $this->assertSame(1, Subscription::count());

        $sub = Subscription::firstOrFail();
        $this->assertSame($order->id, $sub->order_id);
        $this->assertSame($order->client_id, $sub->client_id);
        $this->assertSame(1500000, $sub->contract_value);

        $this->actingAs($user)->get(route('subscriptions.edit', $sub))->assertOk();
    }

    public function test_contract_value_logic_fastwork_vs_langsung(): void
    {
        $fastworkPlatform = Platform::create([
            'name' => 'Fastwork',
            'code' => 'fastwork',
            'commission_percent' => 20.00,
            'fixed_fee' => 0,
            'is_active' => true,
        ]);

        $directPlatform = Platform::create([
            'name' => 'Langsung',
            'code' => 'direct',
            'commission_percent' => 0,
            'fixed_fee' => 0,
            'is_active' => true,
        ]);

        $client = Client::create(['name' => 'Klien B', 'email' => 'b@test.com']);

        $fastworkOrder = Order::create([
            'client_id' => $client->id,
            'platform_id' => $fastworkPlatform->id,
            'title' => 'Order Fastwork',
            'ordered_at' => '2026-01-01',
            'gross_amount' => 2000000,
            'platform_fee' => 400000,
            'net_amount' => 1600000,
        ]);

        $directOrder = Order::create([
            'client_id' => $client->id,
            'platform_id' => $directPlatform->id,
            'title' => 'Order Langsung',
            'ordered_at' => '2026-01-01',
            'gross_amount' => 2000000,
            'platform_fee' => 0,
            'net_amount' => 2000000,
        ]);

        // Fastwork mengambil nilai kotor (gross)
        $this->assertSame(2000000, $fastworkOrder->subscription_contract_value);

        // Langsung mengambil nilai bersih (net)
        $this->assertSame(2000000, $directOrder->subscription_contract_value);
    }

    public function test_datatable_json(): void
    {
        $user = User::factory()->create();
        $order = $this->createOrder();

        Subscription::create([
            'order_id' => $order->id,
            'client_id' => $order->client_id,
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'contract_value' => 1500000,
            'status' => 'active',
        ]);

        $this->actingAs($user)->getJson(route('datatable.subscriptions', ['draw' => 1, 'start' => 0, 'length' => 10]))
            ->assertOk();
    }

    public function test_renew_and_freeze(): void
    {
        $user = User::factory()->create();
        $order = $this->createOrder();

        $sub = Subscription::create([
            'order_id' => $order->id,
            'client_id' => $order->client_id,
            'start_date' => '2025-01-01',
            'end_date' => '2025-12-31',
            'contract_value' => 1200000,
            'status' => 'active',
        ]);

        $this->actingAs($user)->post(route('subscriptions.renew', $sub))->assertRedirect();
        $this->assertSame(2, Subscription::count());

        $this->actingAs($user)->post(route('subscriptions.freeze', $sub), ['freeze_reason' => 'macet']);
    }

    public function test_end_date_is_computed_from_billing_type(): void
    {
        $user = User::factory()->create();
        $order = $this->createOrder();

        foreach ([['annual', '2027-02-28'], ['monthly', '2026-03-31'], ['one_time', '2026-03-01']] as [$type, $expected]) {
            $this->actingAs($user)->post(route('subscriptions.store'), [
                'order_id' => $order->id,
                'client_id' => $order->client_id,
                'billing_type' => $type,
                'start_date' => '2026-03-01',
                'contract_value' => 1000000,
                'status' => 'active',
            ])->assertRedirect();

            $sub = Subscription::where('billing_type', $type)->latest('id')->firstOrFail();
            $this->assertSame($expected, $sub->end_date->toDateString(), "end_date untuk {$type}");
        }
    }

    public function test_renew_creates_invoice_and_reminders_run(): void
    {
        $user = User::factory()->create();
        $order = $this->createOrder();

        $sub = Subscription::create([
            'order_id' => $order->id,
            'client_id' => $order->client_id,
            'billing_type' => 'monthly',
            'start_date' => now()->subMonth()->toDateString(),
            'end_date' => now()->addDays(14)->toDateString(),
            'contract_value' => 500000,
            'status' => 'active',
        ]);

        $this->actingAs($user)->post(route('subscriptions.renew', $sub))->assertRedirect();

        $new = Subscription::where('previous_subscription_id', $sub->id)->firstOrFail();
        $this->assertSame('monthly', $new->billing_type);
        $this->assertSame($order->id, $new->order_id);
        $this->assertSame(500000, $new->contract_value);
        $this->assertDatabaseHas('invoices', ['subscription_id' => $new->id, 'total' => 500000]);

        $this->artisan('subscriptions:remind')->assertSuccessful();
        $this->assertNotNull($sub->fresh()->reminder_14_sent_at);
        $this->assertSame(1, $user->notifications()->count());
    }
}
