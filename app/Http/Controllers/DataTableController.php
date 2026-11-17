<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Order;
use App\Models\Agreement;
use App\Models\Client;
use App\Models\Payment;
use App\Models\Project;
use App\Models\Subscription;
use App\Models\Platform;
use App\Models\ServicePackage;
use App\Models\ExpenseCategory;
use App\Models\DocumentTemplate;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;

class DataTableController extends Controller
{
    public function orders()
    {
        return DataTables::eloquent(Order::with(['client', 'platform'])->select('orders.*'))
            ->addColumn('order_number', fn (Order $order) => 'ORD-'.$order->id)
            ->addColumn('client_name', fn (Order $order) => e($order->client?->name ?? '-'))
            ->addColumn('platform_name', fn (Order $order) => e($order->platform?->name ?? 'Langsung'))
            ->editColumn('ordered_at', fn (Order $order) => $order->ordered_at?->format('d/m/Y') ?? '-')
            ->editColumn('gross_amount', fn (Order $order) => format_rupiah($order->gross_amount))
            ->editColumn('platform_fee', fn (Order $order) => format_rupiah($order->platform_fee))
            ->editColumn('net_amount', fn (Order $order) => format_rupiah($order->net_amount))
            ->editColumn('status', fn (Order $order) => ucfirst(str_replace('_', ' ', $order->status)))
            ->addColumn('payment_status', fn (Order $order) => $order->payment_status === 'paid'
                ? '<span class="badge badge-success">Lunas</span>'
                : '<span class="badge badge-warning">Belum Dibayar</span>')
            ->addColumn('actions', fn (Order $order) => '<a href="'.route('orders.show', $order).'" class="btn btn-sm btn-outline-primary" title="Detail, invoice, PKS, pembayaran"><i class="fas fa-eye"></i></a> <a href="'.route('orders.edit', $order).'" class="btn btn-sm btn-outline-secondary" title="Ubah"><i class="fas fa-edit"></i></a>')
            ->rawColumns(['payment_status', 'actions'])
            ->toJson();
    }

    public function invoices()
    {
        return DataTables::eloquent(Invoice::with('order.client')->select('invoices.*'))
            ->addColumn('order_number', fn (Invoice $invoice) => $invoice->order ? 'ORD-'.$invoice->order->id : '-')
            ->addColumn('order_title', fn (Invoice $invoice) => e($invoice->order?->title ?? '-'))
            ->addColumn('client_name', fn (Invoice $invoice) => e($invoice->order?->client?->name ?? '-'))
            ->editColumn('issued_at', fn (Invoice $invoice) => $invoice->issued_at?->format('d/m/Y') ?? '-')
            ->editColumn('due_date', fn (Invoice $invoice) => $invoice->due_date?->format('d/m/Y') ?? '-')
            ->editColumn('total', fn (Invoice $invoice) => format_rupiah($invoice->total))
            ->editColumn('status', fn (Invoice $invoice) => ucfirst($invoice->status))
            ->addColumn('actions', fn (Invoice $invoice) => '<a href="'.route('invoices.show', $invoice).'" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a><a href="'.route('invoices.print', $invoice).'" target="_blank" class="btn btn-sm btn-outline-secondary ml-1"><i class="fas fa-print"></i></a>')
            ->rawColumns(['actions'])
            ->toJson();
    }

    public function clients() { return DataTables::eloquent(Client::with('platform')->select('clients.*'))->addColumn('platform_name', fn (Client $row) => e($row->platform?->name ?? '-'))->addColumn('actions', fn (Client $row) => $this->editButton('clients.edit', $row).$this->deleteForm('clients.destroy', $row, 'Hapus klien ini?'))->rawColumns(['actions'])->toJson(); }
    public function payments() { return DataTables::eloquent(Payment::with('invoice.order.client')->select('payments.*'))->addColumn('invoice_number', fn (Payment $row) => e($row->invoice?->number ?? '-'))->addColumn('order_number', fn (Payment $row) => $row->invoice?->order ? 'ORD-'.$row->invoice->order->id : '-')->addColumn('client_name', fn (Payment $row) => e($row->invoice?->order?->client?->name ?? '-'))->editColumn('paid_at', fn (Payment $row) => $row->paid_at?->format('d/m/Y') ?? '-')->editColumn('amount', fn (Payment $row) => format_rupiah($row->amount))->editColumn('method', fn (Payment $row) => ucfirst($row->method))->editColumn('reference_no', fn (Payment $row) => e($row->reference_no ?: '-'))->toJson(); }
    public function agreements() { return DataTables::eloquent(Agreement::with('client')->select('agreements.*'))->addColumn('client_name', fn (Agreement $row) => e($row->client?->name ?? '-'))->editColumn('signed_at', fn (Agreement $row) => $row->signed_at?->format('d/m/Y') ?? '-')->editColumn('status', fn (Agreement $row) => ucfirst($row->status))->addColumn('actions', fn (Agreement $row) => $this->viewButton('agreements.show', $row))->rawColumns(['actions'])->toJson(); }
    public function subscriptions() { return DataTables::eloquent(Subscription::with('client')->select('subscriptions.*'))->addColumn('client_name', fn (Subscription $row) => e($row->client?->name ?? '-'))->addColumn('billing_type_label', fn (Subscription $row) => e(ServicePackage::BILLING_TYPES[$row->billing_type] ?? '-'))->editColumn('start_date', fn (Subscription $row) => $row->start_date?->format('d/m/Y') ?? '-')->editColumn('end_date', fn (Subscription $row) => $row->end_date?->format('d/m/Y') ?? '-')->editColumn('contract_value', fn (Subscription $row) => format_rupiah($row->contract_value))->editColumn('status', function (Subscription $row) { $class = $row->calculated_status === 'expired' ? 'danger' : ($row->calculated_status === 'expiring' ? 'warning' : 'success'); return '<span class="badge badge-'.$class.'">'.ucfirst($row->calculated_status).'</span>'; })->addColumn('actions', fn (Subscription $row) => $this->editButton('subscriptions.edit', $row).(!in_array($row->status, ['renewed', 'frozen']) ? '<form method="POST" action="'.route('subscriptions.renew', $row).'" class="d-inline"><input type="hidden" name="_token" value="'.csrf_token().'"><button class="btn btn-sm btn-outline-success" title="Perpanjang"><i class="fas fa-redo"></i></button></form>' : '').$this->deleteForm('subscriptions.destroy', $row, 'Hapus langganan ini?'))->rawColumns(['status', 'actions'])->toJson(); }
    public function projects() { return DataTables::eloquent(Project::with(['client', 'stages'])->select('projects.*'))->addColumn('client_name', fn (Project $row) => e($row->client?->name ?? '-'))->editColumn('status', fn (Project $row) => ucfirst(str_replace('_', ' ', $row->status)))->addColumn('progress', fn (Project $row) => '<div class="progress"><div class="progress-bar" style="width:'.$row->progress_percent.'%">'.$row->progress_percent.'%</div></div>')->editColumn('target_date', fn (Project $row) => $row->target_date?->format('d/m/Y') ?? '-')->addColumn('actions', fn (Project $row) => $this->editButton('projects.edit', $row))->rawColumns(['progress', 'actions'])->toJson(); }
    public function platforms() { return DataTables::eloquent(Platform::withCount('clients'))->editColumn('commission_percent', fn (Platform $row) => number_format((float) $row->commission_percent, 2).'%')->editColumn('fixed_fee', fn (Platform $row) => format_rupiah($row->fixed_fee))->editColumn('is_active', fn (Platform $row) => '<span class="badge badge-'.($row->is_active ? 'success' : 'danger').'">'.($row->is_active ? 'Aktif' : 'Nonaktif').'</span>')->addColumn('actions', fn (Platform $row) => '<a href="'.route('platforms.fee-rules', $row).'" class="btn btn-sm btn-outline-info" title="Tarif potongan"><i class="fas fa-percentage"></i></a> '.$this->editButton('platforms.edit', $row).$this->deleteForm('platforms.destroy', $row, 'Hapus platform ini?'))->rawColumns(['is_active', 'actions'])->toJson(); }
    public function expenseCategories() { return DataTables::eloquent(ExpenseCategory::query())->editColumn('name', fn (ExpenseCategory $row) => '<form method="POST" action="'.route('expense-categories.update', $row).'" class="form-inline"><input type="hidden" name="_token" value="'.csrf_token().'"><input type="hidden" name="_method" value="PUT"><input name="name" value="'.e($row->name).'" class="form-control form-control-sm mr-2" required><button class="btn btn-sm btn-outline-primary">Simpan</button></form>')->addColumn('actions', fn (ExpenseCategory $row) => $this->deleteForm('expense-categories.destroy', $row, 'Hapus kategori ini?'))->rawColumns(['name', 'actions'])->toJson(); }
    public function documentTemplates() { return DataTables::eloquent(DocumentTemplate::query())->editColumn('type', fn (DocumentTemplate $row) => ucfirst($row->type))->editColumn('is_default', fn (DocumentTemplate $row) => $row->is_default ? '<span class="badge badge-success">Default</span>' : '-')->addColumn('actions', fn (DocumentTemplate $row) => $this->editButton('document-templates.edit', $row).$this->deleteForm('document-templates.destroy', $row, 'Hapus template ini?'))->rawColumns(['is_default', 'actions'])->toJson(); }

    public function reports(Request $request)
    {
        $from = $request->date('from')?->startOfDay() ?? now()->startOfMonth();
        $to = $request->date('to')?->endOfDay() ?? now()->endOfMonth();
        return DataTables::eloquent(Order::with('platform')->whereBetween('ordered_at', [$from, $to]))
            ->editColumn('ordered_at', fn (Order $row) => $row->ordered_at?->format('d/m/Y') ?? '-')
            ->addColumn('platform_name', fn (Order $row) => e($row->platform?->name ?? 'Langsung'))
            ->editColumn('status', fn (Order $row) => ucfirst(str_replace('_', ' ', $row->status)))
            ->editColumn('gross_amount', fn (Order $row) => format_rupiah($row->gross_amount))
            ->editColumn('platform_fee', fn (Order $row) => format_rupiah($row->platform_fee))
            ->editColumn('net_amount', fn (Order $row) => format_rupiah($row->net_amount))
            ->toJson();
    }

    private function editButton(string $route, $model): string
    {
        return '<a href="'.route($route, $model).'" class="btn btn-sm btn-outline-secondary"><i class="fas fa-edit"></i></a> ';
    }

    private function viewButton(string $route, $model): string
    {
        return '<a href="'.route($route, $model).'" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a> ';
    }

    private function deleteForm(string $route, $model, string $confirm): string
    {
        return '<form class="d-inline" method="POST" action="'.route($route, $model).'"><input type="hidden" name="_token" value="'.csrf_token().'"><input type="hidden" name="_method" value="DELETE"><button class="btn btn-sm btn-outline-danger" onclick="return confirm(\''.addslashes($confirm).'\')"><i class="fas fa-trash"></i></button></form>';
    }
}
