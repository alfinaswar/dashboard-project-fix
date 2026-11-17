<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Order;
use App\Models\OrderFeeItem;
use App\Models\Platform;
use App\Models\User;
use App\Services\OrderDocumentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceShowTest extends TestCase
{
    use RefreshDatabase;

    private function order(): Order
    {
        $client = Client::create(['name' => 'Klien A', 'contact_name' => 'X', 'email' => 'a@b.c', 'address' => 'Jl. Mawar 1']);
        $platform = Platform::create(['name' => 'Fastwork', 'code' => 'fw-1', 'commission_percent' => 5, 'fixed_fee' => 0]);

        $order = Order::create([
            'client_id' => $client->id,
            'platform_id' => $platform->id,
            'title' => 'Desain Logo',
            'ordered_at' => '2026-03-01',
            'status' => 'completed',
            'revision_count' => 0,
            'gross_amount' => 1000000,
            'platform_fee' => 50000,
            'net_amount' => 950000,
        ]);
        OrderFeeItem::create(['order_id' => $order->id, 'label' => 'Komisi', 'amount_type' => 'amount', 'amount' => 50000, 'position' => 0]);

        return $order;
    }

    public function test_invoice_page_shows_summary_and_history(): void
    {
        $user = User::factory()->create();
        $order = $this->order();
        $invoice = app(OrderDocumentService::class)->ensureInvoice($order);

        $this->actingAs($user)->get(route('invoices.show', $invoice))
            ->assertOk()
            ->assertSee('Klien A')
            ->assertSee('Jl. Mawar 1')
            ->assertSee('Desain Logo')
            ->assertSee($invoice->number)
            ->assertSee('Sisa Tagihan')
            ->assertSee('Riwayat Pembayaran')
            ->assertSee('Belum ada pembayaran masuk.');
    }

    public function test_invoice_page_renders_for_payment_proof_without_history(): void
    {
        $user = User::factory()->create();
        $order = $this->order();
        $invoice = app(OrderDocumentService::class)->ensureInvoice($order);

        $payment = \App\Models\Payment::create([
            'invoice_id' => $invoice->id,
            'paid_at' => '2026-03-05',
            'amount' => 400000,
            'method' => 'transfer',
            'reference_no' => 'TRX-1',
        ]);
        $proof = \App\Models\Invoice::create([
            'number' => 'INV-PAY-1',
            'source' => 'generated',
            'order_id' => $order->id,
            'payment_id' => $payment->id,
            'issued_at' => '2026-03-05',
            'due_date' => '2026-03-05',
            'subtotal' => 400000,
            'tax_amount' => 0,
            'total' => 400000,
            'status' => 'paid',
        ]);

        $this->actingAs($user)->get(route('invoices.show', $proof))
            ->assertOk()
            ->assertSee('BUKTI PEMBAYARAN')
            ->assertSee('TRX-1')
            ->assertDontSee('Riwayat Pembayaran');
    }
}
