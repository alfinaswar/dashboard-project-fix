<?php
namespace App\Http\Controllers;
use App\Models\Invoice; use App\Models\Order; use Barryvdh\DomPDF\Facade\Pdf; use Illuminate\Support\Facades\Storage;
/**
 * Hanya rekap dan lihat. Pembuatan invoice otomatis dari order (OrderDocumentService).
 */
class InvoiceController extends Controller
{
    public function index() { $invoices=Invoice::with('order.client')->latest('issued_at')->paginate(15); return view('invoices.index',compact('invoices')); }
    public function show(Invoice $invoice) { $invoice->load('order.client','order.orderInvoices.payments','payment'); return view('invoices.show',compact('invoice')); }
    public function print(Invoice $invoice) { $invoice->load('order.client','order.orderInvoices.payments','payment'); return view('invoices.print',compact('invoice')); }
    public function download(Invoice $invoice) { $path=$invoice->source==='uploaded'?$invoice->file_path:$invoice->pdf_path; abort_unless($path && Storage::disk('private')->exists($path),404); return Storage::disk('private')->download($path); }
}
