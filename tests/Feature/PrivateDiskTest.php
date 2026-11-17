<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PrivateDiskTest extends TestCase
{
    use RefreshDatabase;

    public function test_private_disk_is_configured(): void
    {
        $this->assertSame(storage_path('app/private'), config('filesystems.disks.private.root'));
        $this->assertSame('local', config('filesystems.disks.private.driver'));
    }

    public function test_private_disk_can_write_and_read(): void
    {
        Storage::fake('private');
        Storage::disk('private')->put('probe.txt', 'ok');

        $this->assertTrue(Storage::disk('private')->exists('probe.txt'));
        $this->assertSame('ok', Storage::disk('private')->get('probe.txt'));
    }

    public function test_generated_invoice_stores_pdf_on_private_disk(): void
    {
        Storage::fake('private');
        $user = User::factory()->create();
        $client = Client::create(['name' => 'Klien A']);
        $platform = \App\Models\Platform::create(['name' => 'Fastwork', 'code' => 'fw-'.uniqid()]);
        $order = Order::create([
            'client_id' => $client->id, 'platform_id' => $platform->id, 'title' => 'Desain',
            'ordered_at' => '2026-03-01', 'status' => 'completed', 'gross_amount' => 100000,
        ]);

        $this->actingAs($user)->post(route('invoices.store'), [
            'number' => '',
            'order_id' => $order->id,
            'source' => 'generated',
            'issued_at' => '2026-03-02',
            'due_date' => '2026-03-16',
            'subtotal' => 100000,
            'tax_amount' => 0,
            'status' => 'unpaid',
        ])->assertRedirect(route('invoices.index'));

        $invoice = Invoice::firstOrFail();
        $this->assertNotNull($invoice->pdf_path);
        $this->assertTrue(Storage::disk('private')->exists($invoice->pdf_path));

        $this->actingAs($user)->get(route('invoices.download', $invoice))->assertOk();
    }
}
