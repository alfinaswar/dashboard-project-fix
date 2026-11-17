<?php

namespace Tests\Feature;

use App\Models\Agreement;
use App\Models\Client;
use App\Models\DocumentTemplate;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Platform;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OrderShowTest extends TestCase
{
    use RefreshDatabase;

    private function order(User $user, array $extra = []): Order
    {
        $client = Client::create(['name' => 'Klien A']);
        $platform = Platform::create(['name' => 'Fastwork', 'code' => 'fw-'.uniqid()]);

        $this->actingAs($user)->post(route('orders.store'), array_merge([
            'client_id' => $client->id,
            'platform_id' => $platform->id,
            'title' => 'Desain Logo',
            'ordered_at' => '2026-03-01',
            'status' => 'completed',
            'revision_count' => 0,
            'gross_amount' => 1000000,
        ], $extra))->assertRedirect();

        return Order::latest('id')->firstOrFail();
    }

    public function test_saving_order_creates_invoice_and_agreement(): void
    {
        Storage::fake('private');
        DocumentTemplate::create(['type' => 'agreement', 'name' => 'PKS', 'body' => '<p>{{ nomor_pks }} {{ judul_pekerjaan }}</p>', 'is_default' => true]);
        DocumentTemplate::create(['type' => 'invoice', 'name' => 'Invoice', 'body' => '<p>{{ nomor_invoice }}</p>', 'is_default' => true]);
        $user = User::factory()->create();

        $order = $this->order($user);

        $invoice = Invoice::where('order_id', $order->id)->firstOrFail();
        $this->assertSame(1000000, $invoice->total);
        $this->assertSame('unpaid', $invoice->status);

        $agreement = Agreement::where('order_id', $order->id)->firstOrFail();
        $this->assertSame('draft', $agreement->status);
        $this->assertStringContainsString('Desain Logo', $agreement->versions()->firstOrFail()->body_rendered);
    }

    public function test_order_detail_page_shows_documents_and_payment_form(): void
    {
        $user = User::factory()->create();
        $order = $this->order($user);

        $this->actingAs($user)->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee('ORD-'.$order->id)
            ->assertSee('Pembayaran Masuk')
            ->assertSee('Invoice')
            ->assertSee('Perjanjian Kerjasama');
    }

    public function test_payment_from_order_marks_invoice_paid_and_rejects_overflow(): void
    {
        $user = User::factory()->create();
        $order = $this->order($user);
        $invoice = Invoice::where('order_id', $order->id)->firstOrFail();

        $this->actingAs($user)->post(route('orders.payments.store', $order), [
            'paid_at' => '2026-03-05', 'amount' => 400000, 'method' => 'transfer',
        ])->assertRedirect(route('orders.show', $order));

        $this->assertSame('partial', $invoice->refresh()->status);
        $this->assertSame(400000, $order->fresh()->invoices()->with('payments')->get()->flatMap->payments->sum('amount'));

        // Melebihi sisa tagihan ditolak.
        $this->actingAs($user)->post(route('orders.payments.store', $order), [
            'paid_at' => '2026-03-06', 'amount' => 700000, 'method' => 'transfer',
        ])->assertSessionHas('error');

        $this->assertSame(1, Payment::count());

        $this->actingAs($user)->post(route('orders.payments.store', $order), [
            'paid_at' => '2026-03-07', 'amount' => 600000, 'method' => 'cash',
        ])->assertRedirect(route('orders.show', $order));

        $this->assertSame('paid', $invoice->refresh()->status);
        $this->assertSame('paid', $order->fresh()->payment_status);
    }

    public function test_invoice_number_uses_order_format_and_resets_monthly(): void
    {
        $user = User::factory()->create();

        $march = $this->order($user, ['ordered_at' => '2026-03-01']);
        $march2 = $this->order($user, ['ordered_at' => '2026-03-20']);
        $april = $this->order($user, ['ordered_at' => '2026-04-02']);

        $this->assertSame('ORD/2026/03/0001', $march->invoices()->firstOrFail()->number);
        $this->assertSame('ORD/2026/03/0002', $march2->invoices()->firstOrFail()->number);
        $this->assertSame('ORD/2026/04/0001', $april->invoices()->firstOrFail()->number);
    }

    public function test_each_payment_gets_its_own_invoice_linked_to_order_invoice(): void
    {
        $user = User::factory()->create();
        $order = $this->order($user, ['ordered_at' => '2026-03-01']);
        $orderInvoice = Invoice::where('order_id', $order->id)->firstOrFail();

        $this->actingAs($user)->post(route('orders.payments.store', $order), [
            'paid_at' => '2026-03-05', 'amount' => 400000, 'method' => 'transfer',
        ])->assertRedirect(route('orders.show', $order));

        $this->actingAs($user)->post(route('orders.payments.store', $order), [
            'paid_at' => '2026-03-07', 'amount' => 600000, 'method' => 'cash',
        ])->assertRedirect(route('orders.show', $order));

        $paymentInvoices = Invoice::where('payment_id', '!=', null)->orderBy('id')->get();

        $this->assertCount(2, $paymentInvoices);
        $this->assertSame('ORD/2026/03/0001-B1', $paymentInvoices[0]->number);
        $this->assertSame(400000, $paymentInvoices[0]->total);
        $this->assertSame('ORD/2026/03/0001-B2', $paymentInvoices[1]->number);
        $this->assertSame(600000, $paymentInvoices[1]->total);

        // Tagihan order tetap dihitung dari invoice order, bukan invoice pembayaran.
        $this->assertSame(1000000, $orderInvoice->refresh()->total);

        // Tiap baris pembayaran punya tombol lihat dan cetak invoice.
        $this->actingAs($user)->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee(route('invoices.show', $paymentInvoices[0]), false)
            ->assertSee(route('invoices.print', $paymentInvoices[1]), false);
    }

    public function test_order_invoice_page_lists_every_payment(): void
    {
        $user = User::factory()->create();
        $order = $this->order($user, ['ordered_at' => '2026-03-01']);
        $orderInvoice = Invoice::where('order_id', $order->id)->firstOrFail();

        foreach ([['2026-03-05', 400000, 'transfer'], ['2026-03-07', 600000, 'cash']] as [$paidAt, $amount, $method]) {
            $this->actingAs($user)->post(route('orders.payments.store', $order), [
                'paid_at' => $paidAt, 'amount' => $amount, 'method' => $method,
            ]);
        }

        $this->actingAs($user)->get(route('invoices.show', $orderInvoice))
            ->assertOk()
            ->assertSee('Riwayat Pembayaran')
            ->assertSee('ORD/2026/03/0001-B1')
            ->assertSee('ORD/2026/03/0001-B2');
    }

    public function test_withdrawal_feature_is_removed(): void
    {
        $user = User::factory()->create();

        $this->assertFalse(\Illuminate\Support\Facades\Route::has('withdrawals.index'));
        $this->assertFalse(\Illuminate\Support\Facades\Route::has('orders.payout'));
        $this->assertFalse(\Illuminate\Support\Facades\Schema::hasColumn('orders', 'payout_status'));
        $this->assertFalse(\Illuminate\Support\Facades\Schema::hasColumn('platforms', 'withdrawal_fee'));

        // Halaman rekap tetap bisa dibuka, tapi tidak ada di menu.
        $this->actingAs($user)->get(route('payments.index'))->assertOk()->assertDontSee('Penarikan Saldo');
    }
}
