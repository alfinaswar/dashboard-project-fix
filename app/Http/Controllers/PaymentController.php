<?php
namespace App\Http\Controllers;
use App\Http\Requests\StorePaymentRequest; use App\Models\Invoice; use App\Models\Payment; use Illuminate\Support\Facades\DB;
class PaymentController extends Controller
{
    public function index() { $payments=Payment::with('invoice')->latest('paid_at')->paginate(15); return view('payments.index',compact('payments')); }
    public function create() { $invoices=Invoice::whereIn('status',['draft','sent','unpaid','partial'])->orderBy('due_date')->get(); return view('payments.form',compact('invoices')); }
    public function store(StorePaymentRequest $request) { $data=$request->validated(); $invoice=Invoice::findOrFail($data['invoice_id']); $paid=(int)$invoice->payments()->sum('amount'); if($paid+(int)$data['amount']>$invoice->total) return back()->withInput()->with('error','Pembayaran melebihi sisa tagihan.'); DB::transaction(function() use($data,$invoice){ Payment::create($data); $total=(int)$invoice->payments()->sum('amount'); $invoice->update(['status'=>$total===$invoice->total?'paid':'partial']); }); return redirect()->route('payments.index')->with('success','Pembayaran berhasil dicatat.'); }
}
