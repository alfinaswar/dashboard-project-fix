<?php
namespace App\Http\Controllers;
use App\Http\Requests\StoreOrderRequest;
use App\Models\Client; use App\Models\Order; use App\Models\Platform; use App\Models\ServicePackage;
use App\Services\OrderDocumentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class OrderController extends Controller
{
    public function __construct(private OrderDocumentService $documents) {}

    public function index() { $orders=Order::with(['client','platform'])->latest('ordered_at')->paginate(15); return view('orders.index',compact('orders')); }
    public function create() { return view('orders.form',['order'=>new Order(),'clients'=>Client::orderBy('name')->get(),'platforms'=>Platform::where('is_active',true)->orderBy('name')->get(),'packages'=>ServicePackage::where('is_active',true)->orderBy('name')->get()]); }

    /**
     * Detail order: semua dokumen (invoice, PKS, pembayaran masuk) dikelola dari sini.
     */
    public function show(Order $order)
    {
        $order->load(['client', 'platform', 'servicePackage', 'feeItems', 'invoices.payments.paymentInvoice', 'agreements.versions']);

        // Order lama tanpa dokumen tetap dilengkapi saat dibuka.
        $invoice = $this->documents->ensureInvoice($order);
        $agreement = $this->documents->ensureAgreement($order);
        $order->setRelation('invoices', $order->orderInvoices()->with('payments.paymentInvoice')->latest('id')->get());

        return view('orders.show', [
            'order' => $order,
            'invoice' => $invoice,
            'agreement' => $agreement,
            'paid' => $this->documents->paidFor($order),
            'outstanding' => $this->documents->outstandingFor($order),
        ]);
    }

    public function store(StoreOrderRequest $request)
    {
        $data=$request->validated();
        $items=$data['fee_items'] ?? [];
        unset($data['fee_items']);
        $platform=Platform::findOrFail($data['platform_id']);

        if (empty($items)) {
            $rule=$platform->feeRuleFor($data['ordered_at']);
            $items=$this->defaultFeeItems($platform,$data,$rule);
            $data['platform_fee_rule_id']=$rule?->id;
        } else {
            $data['platform_fee_rule_id']=null;
        }

        $data['platform_fee']=$this->feeTotal($items,(int)$data['gross_amount']);
        $data['net_amount']=(int)$data['gross_amount']-$data['platform_fee']-(int)($data['other_fee']??0);
        unset($data['platform_fee_override'],$data['platform_fee_override_reason']);

        $order=DB::transaction(function() use($data,$items){
            $order=Order::create($data);
            $this->syncFeeItems($order,$items);
            $this->documents->ensureInvoice($order);
            $this->documents->ensureAgreement($order);

            return $order;
        });

        return redirect()->route('orders.show', $order)->with('success','Order berhasil ditambahkan. Invoice dan perjanjian sudah dibuat.');
    }

    public function edit(Order $order) { $order->load('feeItems'); return view('orders.form',['order'=>$order,'clients'=>Client::orderBy('name')->get(),'platforms'=>Platform::where('is_active',true)->orderBy('name')->get(),'packages'=>ServicePackage::where('is_active',true)->orderBy('name')->get()]); }

    public function update(StoreOrderRequest $request, Order $order)
    {
        $data=$request->validated();
        $items=$data['fee_items'] ?? [];
        unset($data['fee_items']);
        $platform=Platform::findOrFail($data['platform_id']);

        $data['platform_fee_rule_id']=empty($items)?$platform->feeRuleFor($data['ordered_at'])?->id:null;
        $data['platform_fee']=$this->feeTotal($items,(int)$data['gross_amount']);
        $data['net_amount']=(int)$data['gross_amount']-$data['platform_fee']-(int)($data['other_fee']??0);
        unset($data['platform_fee_override'],$data['platform_fee_override_reason']);

        DB::transaction(function() use($data,$items,$order){
            $order->update($data);
            $this->syncFeeItems($order,$items);
        });

        return redirect()->route('orders.show', $order)->with('success','Order berhasil diperbarui.');
    }

    /**
     * Potongan awal untuk order baru: pakai tarif platform yang berlaku, atau formula lama.
     */
    private function defaultFeeItems(Platform $platform, array $data, ?\App\Models\PlatformFeeRule $rule = null): array
    {
        $gross=(int)$data['gross_amount'];

        return [[
            'label' => $rule ? 'Potongan '.$platform->name.' (tarif '.$rule->effective_from->format('d/m/Y').')' : 'Potongan '.$platform->name,
            'amount_type' => 'amount',
            'amount' => $rule ? $rule->calculateFee($gross) : $platform->calculateFee($gross),
        ]];
    }

    /**
     * Total potongan: baris nominal ditambah baris persen x nilai kotor.
     */
    private function feeTotal(array $items, int $grossAmount): int
    {
        $total = 0;
        foreach ($items as $item) {
            $amount = (int) $item['amount'];
            $total += ($item['amount_type'] ?? 'amount') === 'percent'
                ? (int) round($grossAmount * $amount / 100)
                : $amount;
        }

        return $total;
    }

    private function syncFeeItems(Order $order, array $items): void
    {
        $order->feeItems()->delete();
        foreach (array_values($items) as $position => $item) {
            $order->feeItems()->create([
                'label' => $item['label'],
                'amount_type' => $item['amount_type'] ?? 'amount',
                'amount' => (int) $item['amount'],
                'position' => $position,
            ]);
        }
    }
}
