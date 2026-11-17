<?php
namespace App\Http\Controllers;
use App\Http\Requests\StoreInvoiceRequest; use App\Models\DocumentTemplate; use App\Models\Invoice; use App\Models\Order; use Barryvdh\DomPDF\Facade\Pdf; use Illuminate\Support\Facades\Storage;
class InvoiceController extends Controller
{
    public function index() { $invoices=Invoice::with('order.client')->latest('issued_at')->paginate(15); return view('invoices.index',compact('invoices')); }
    public function create() { return view('invoices.form',['orders'=>Order::with('client')->orderByDesc('ordered_at')->get(),'templates'=>DocumentTemplate::where('type','invoice')->orderBy('name')->get()]); }
    public function store(StoreInvoiceRequest $request) { $data=$request->validated(); $data['number']=$data['number'] ?: $this->nextNumber($data['issued_at']); $data['total']=(int)$data['subtotal']+(int)$data['tax_amount']; if($request->hasFile('file')) $data['file_path']=$request->file('file')->store('invoices','private'); unset($data['file']); $invoice=Invoice::create($data); if($invoice->source==='generated') { $invoice->update(['pdf_path'=>$this->makePdf($invoice)]); } return redirect()->route('invoices.index')->with('success','Invoice berhasil dibuat.'); }
    public function show(Invoice $invoice) { return view('invoices.show',compact('invoice')); }
    public function download(Invoice $invoice) { $path=$invoice->source==='uploaded'?$invoice->file_path:$invoice->pdf_path; abort_unless($path && Storage::disk('private')->exists($path),404); return Storage::disk('private')->download($path); }
    private function nextNumber(string $issuedAt): string { $date=\Carbon\Carbon::parse($issuedAt); $prefix=sprintf('INV/%s/%s/',$date->format('Y'),$date->format('m')); $last=Invoice::where('number','like',$prefix.'%')->orderByDesc('number')->value('number'); return $prefix.str_pad((string)(($last?(int)substr($last,-4):0)+1),4,'0',STR_PAD_LEFT); }
    private function makePdf(Invoice $invoice): string { $path='invoices/'.$invoice->number.'.pdf'; Storage::disk('private')->put($path,Pdf::loadView('invoices.pdf',compact('invoice'))->output()); return $path; }
}
