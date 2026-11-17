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

        $this->actingAs($user)
            ->from(route('service-packages.create'))
            ->post(route('service-packages.store'), [
                'name' => 'Paket A',
                'default_price' => 'abc',
                'default_duration_months' => 12,
                'billing_type' => 'one_time',
            ])->assertSessionHasErrors(['default_price' => 'Harga default harus berupa bilangan bulat.']);
    }
}
