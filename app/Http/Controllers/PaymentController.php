<?php
namespace App\Http\Controllers;
use App\Models\Invoice; use App\Models\Order; use App\Models\Payment; use App\Services\OrderDocumentService; use Illuminate\Http\Request; use Illuminate\Support\Facades\DB;
class PaymentController extends Controller
{
    public function __construct(private OrderDocumentService $documents) {}

    public function index() { $payments=Payment::with('invoice.order.client')->latest('paid_at')->paginate(15); return view('payments.index',compact('payments')); }

    /**
     * Pembayaran selalu dicatat dari detail order, tidak ada input invoice manual.
     */
    public function create(Request $request) { return redirect()->route('orders.index')->with('error','Pembayaran dicatat dari halaman detail order.'); }

    public function store(Request $request) { return redirect()->route('orders.index')->with('error','Pembayaran dicatat dari halaman detail order.'); }

    /**
     * Catat pembayaran masuk untuk satu order. Invoice dibuat otomatis kalau belum ada.
     */
    public function storeForOrder(Request $request, Order $order)
    {
        $data = $request->validate([
            'paid_at' => ['required', 'date'],
            'amount' => ['required', 'integer', 'min:1'],
            'method' => ['required', 'in:transfer,cash,other'],
            'reference_no' => ['nullable', 'string', 'max:255'],
        ], [
            'paid_at.required' => 'Tanggal pembayaran wajib diisi.',
            'amount.required' => 'Jumlah pembayaran wajib diisi.',
            'amount.min' => 'Jumlah pembayaran minimal Rp 1.',
            'method.required' => 'Metode pembayaran wajib dipilih.',
        ]);

        $invoice = $this->documents->ensureInvoice($order);
        $sisa = $this->documents->outstandingFor($order);

        if ((int) $data['amount'] > $sisa) {
            return back()->withInput()->with('error', 'Jumlah pembayaran melebihi sisa tagihan '.format_rupiah($sisa).'.');
        }

        DB::transaction(function () use ($data, $invoice) {
            Payment::create([...$data, 'invoice_id' => $invoice->id]);
            $lunas = $invoice->paidAmount() >= $invoice->total;
            $invoice->update(['status' => $lunas ? 'paid' : 'partial']);
        });

        return redirect()->route('orders.show', $order)->with('success', 'Pembayaran masuk berhasil dicatat.');
    }
}
