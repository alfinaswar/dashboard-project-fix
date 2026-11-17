<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SweetAlertErrorTest extends TestCase
{
    use RefreshDatabase;

    public function test_validation_errors_render_as_sweet_alert_payload(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->from(route('clients.create'))
            ->post(route('clients.store'), ['name' => '']);

        $response->assertRedirect(route('clients.create'));
        $response->assertSessionHasErrors(['name']);

        $html = $this->actingAs($user)->get(route('clients.create'))->getContent();

        $this->assertStringContainsString('id="sweet-alerts"', $html);
        $this->assertStringContainsString('Data belum lengkap', $html);
        $this->assertStringContainsString('Nama klien wajib diisi.', $html);
        // Pesan inline Bootstrap tidak boleh tampil lagi.
        $this->assertStringNotContainsString('invalid-feedback', $html);
    }

    public function test_unknown_validation_rule_falls_back_to_indonesian_message(): void
    {
        $user = User::factory()->create();
        $client = \App\Models\Client::create(['name' => 'Klien A', 'contact_name' => 'X', 'email' => 'a@b.c']);
        $platform = \App\Models\Platform::create(['name' => 'Fastwork', 'code' => 'fw-'.uniqid(), 'commission_percent' => 5, 'fixed_fee' => 0]);

        $this->actingAs($user)
            ->from(route('orders.create'))
            ->post(route('orders.store'), [
                'client_id' => $client->id,
                'platform_id' => $platform->id,
                'title' => 'Order A',
                'ordered_at' => '2026-03-01',
                'status' => 'draft',
                'revision_count' => 0,
                'gross_amount' => 'abc',
            ])->assertSessionHasErrors(['gross_amount']);

        $this->assertSame('Nilai kotor harus berupa bilangan bulat.', session('errors')->first('gross_amount'));
    }
}
