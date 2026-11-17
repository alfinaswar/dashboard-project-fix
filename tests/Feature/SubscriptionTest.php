<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionTest extends TestCase
{
    use RefreshDatabase;

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

    public function test_store_creates(): void
    {
        $user = User::factory()->create();
        $client = Client::create(['name' => 'Klien A', 'contact_name' => 'X', 'email' => 'a@b.c']);

        $this->actingAs($user)->post(route('subscriptions.store'), [
            'client_id' => $client->id,
            'billing_type' => 'annual',
            'start_date' => '2026-01-01',
            'contract_value' => 1200000,
            'status' => 'active',
        ])->assertRedirect(route('subscriptions.index'));

        $this->assertSame(1, Subscription::count());

        $sub = Subscription::firstOrFail();
        $this->actingAs($user)->get(route('subscriptions.edit', $sub))->assertOk();
    }

    public function test_datatable_json(): void
    {
        $user = User::factory()->create();
        $client = Client::create(['name' => 'Klien A', 'contact_name' => 'X', 'email' => 'a@b.c']);
        Subscription::create([
            'client_id' => $client->id,
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'contract_value' => 1200000,
            'status' => 'active',
        ]);

        $this->actingAs($user)->getJson(route('datatable.subscriptions', ['draw' => 1, 'start' => 0, 'length' => 10]))
            ->assertOk();
    }

    public function test_renew_and_freeze(): void
    {
        $user = User::factory()->create();
        $client = Client::create(['name' => 'Klien A', 'contact_name' => 'X', 'email' => 'a@b.c']);
        $sub = Subscription::create([
            'client_id' => $client->id,
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
        $client = Client::create(['name' => 'Klien A', 'contact_name' => 'X', 'email' => 'a@b.c']);

        foreach ([['annual', '2027-02-28'], ['monthly', '2026-03-31'], ['one_time', '2026-03-01']] as [$type, $expected]) {
            $this->actingAs($user)->post(route('subscriptions.store'), [
                'client_id' => $client->id,
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
        $client = Client::create(['name' => 'Klien A', 'contact_name' => 'X', 'email' => 'a@b.c']);
        $sub = Subscription::create([
            'client_id' => $client->id,
            'billing_type' => 'monthly',
            'start_date' => now()->subMonth()->toDateString(),
            'end_date' => now()->addDays(14)->toDateString(),
            'contract_value' => 500000,
            'status' => 'active',
        ]);

        $this->actingAs($user)->post(route('subscriptions.renew', $sub))->assertRedirect();

        $new = Subscription::where('previous_subscription_id', $sub->id)->firstOrFail();
        $this->assertSame('monthly', $new->billing_type);
        $this->assertSame(500000, $new->contract_value);
        $this->assertDatabaseHas('invoices', ['subscription_id' => $new->id, 'total' => 500000]);

        $this->artisan('subscriptions:remind')->assertSuccessful();
        $this->assertNotNull($sub->fresh()->reminder_14_sent_at);
        $this->assertSame(1, $user->notifications()->count());
    }
}
