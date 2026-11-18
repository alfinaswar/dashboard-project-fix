<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSubscriptionRequest;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SubscriptionController extends Controller
{
    /** Lama masa aktif untuk tiap jenis langganan. */
    public const DURATIONS = Subscription::DURATIONS;

    public function index()
    {
        $subscriptions = Subscription::with(['order.client', 'order.platform', 'client'])->latest('end_date')->paginate(15);

        return view('subscriptions.index', compact('subscriptions'));
    }

    public function create()
    {
        $orders = Order::with(['client', 'platform'])->latest('ordered_at')->get();

        return view('subscriptions.form', [
            'subscription' => new Subscription([
                'start_date' => now()->toDateString(),
                'status' => 'active',
                'billing_type' => 'annual',
                'contract_value' => 0,
            ]),
            'orders' => $orders,
            'clients' => Client::orderBy('name')->get(),
        ]);
    }

    public function store(StoreSubscriptionRequest $request)
    {
        $data = $this->withPeriod($request->validated());

        if (!empty($data['order_id'])) {
            $order = Order::with('platform')->find($data['order_id']);
            if ($order) {
                $data['client_id'] = $order->client_id;
                // Jika nilai kontrak kosong atau 0, otomatis ambil dari order sesuai platform
                if (empty($data['contract_value'])) {
                    $data['contract_value'] = $order->subscription_contract_value;
                }
            }
        }

        Subscription::create($data);

        return redirect()->route('subscriptions.index')->with('success', 'Langganan berhasil ditambahkan.');
    }

    public function edit(Subscription $subscription)
    {
        $orders = Order::with(['client', 'platform'])->latest('ordered_at')->get();

        return view('subscriptions.form', [
            'subscription' => $subscription->load(['order.client', 'order.platform', 'client']),
            'orders' => $orders,
            'clients' => Client::orderBy('name')->get(),
        ]);
    }

    public function update(StoreSubscriptionRequest $request, Subscription $subscription)
    {
        $data = $this->withPeriod($request->validated());

        if (!empty($data['order_id'])) {
            $order = Order::find($data['order_id']);
            if ($order) {
                $data['client_id'] = $order->client_id;
            }
        }

        $subscription->update($data);

        return redirect()->route('subscriptions.index')->with('success', 'Langganan berhasil diperbarui.');
    }

    public function renew(Subscription $subscription)
    {
        abort_if($subscription->status === 'frozen', 403, 'Langganan dibekukan tidak dapat diperpanjang.');

        $start = $subscription->renewalStartDate();
        $months = $subscription->durationMonths();
        $new = DB::transaction(function () use ($subscription, $start, $months) {
            $new = Subscription::create([
                'order_id' => $subscription->order_id,
                'client_id' => $subscription->client_id ?? $subscription->order?->client_id,
                'billing_type' => $subscription->billing_type,
                'previous_subscription_id' => $subscription->id,
                'start_date' => $start->toDateString(),
                'end_date' => $this->periodEnd($start, $months)->toDateString(),
                'contract_value' => $subscription->contract_value,
                'status' => 'active',
            ]);

            $subscription->update(['status' => 'renewed']);

            $prefix = 'INV/'.now()->format('Y/m/');
            $number = $prefix.str_pad((string) (Invoice::where('number', 'like', $prefix.'%')->count() + 1), 4, '0', STR_PAD_LEFT);

            Invoice::create([
                'number' => $number,
                'source' => 'generated',
                'subscription_id' => $new->id,
                'order_id' => $new->order_id,
                'issued_at' => now()->toDateString(),
                'due_date' => now()->addDays((int) setting('invoice_due_days', 14))->toDateString(),
                'subtotal' => $new->contract_value,
                'tax_amount' => 0,
                'total' => $new->contract_value,
                'status' => 'unpaid',
            ]);

            return $new;
        });

        return redirect()->route('subscriptions.index')->with('success', 'Langganan berhasil diperpanjang dan tagihan dibuat.');
    }

    public function freeze(Subscription $subscription)
    {
        $subscription->update(['status' => 'frozen', 'freeze_reason' => request('freeze_reason')]);

        return back()->with('success', 'Langganan berhasil dibekukan.');
    }

    public function destroy(Subscription $subscription)
    {
        $subscription->delete();

        return redirect()->route('subscriptions.index')->with('success', 'Langganan berhasil dihapus.');
    }

    /** Hitung tanggal berakhir kalau user mengosongkannya. */
    private function withPeriod(array $data): array
    {
        $start = Carbon::parse($data['start_date']);
        $months = Subscription::DURATIONS[$data['billing_type']] ?? 12;

        $data['end_date'] = $data['end_date'] ?? $this->periodEnd($start, $months)->toDateString();

        return $data;
    }

    /**
     * Tanggal berakhir = H-1 dari penambahan masa aktif.
     * Tanpa masa aktif (sekali beli), tanggal berakhir sama dengan tanggal mulai.
     */
    private function periodEnd(Carbon $start, int $months): Carbon
    {
        return $months > 0 ? $start->copy()->addMonthsNoOverflow($months)->subDay() : $start->copy();
    }
}
