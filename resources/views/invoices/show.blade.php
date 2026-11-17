@extends('layouts.admin')

@section('title', 'Detail Invoice')
@section('page_title', 'Invoice '.$invoice->number)

@section('page_actions')
    @if($invoice->order)
        <a href="{{ route('orders.show', $invoice->order) }}" class="btn btn-default"><i class="fas fa-arrow-left mr-1"></i> Kembali ke Order</a>
    @endif
    <a href="{{ route('invoices.print', $invoice) }}" target="_blank" class="btn btn-outline-secondary"><i class="fas fa-print mr-1"></i> Cetak</a>
@endsection

@section('content')
<div class="card card-outline card-primary">
    <div class="card-body">
        <dl class="row">
            <dt class="col-sm-3">Nomor</dt><dd class="col-sm-9">{{ $invoice->number }}</dd>
            <dt class="col-sm-3">{{ $invoice->payment ? 'Jenis' : 'Order' }}</dt>
            <dd class="col-sm-9">{{ $invoice->payment ? 'Bukti pembayaran' : ($invoice->order?->title ?: '-') }}</dd>
            <dt class="col-sm-3">Klien</dt><dd class="col-sm-9">{{ $invoice->order?->client?->name ?: '-' }}</dd>
            <dt class="col-sm-3">Terbit</dt><dd class="col-sm-9">{{ format_date($invoice->issued_at) }}</dd>
            <dt class="col-sm-3">Jatuh Tempo</dt><dd class="col-sm-9">{{ format_date($invoice->due_date) }}</dd>
            <dt class="col-sm-3">Subtotal</dt><dd class="col-sm-9">{{ format_rupiah($invoice->subtotal) }}</dd>
            <dt class="col-sm-3">Pajak</dt><dd class="col-sm-9">{{ format_rupiah($invoice->tax_amount) }}</dd>
            <dt class="col-sm-3">Total</dt><dd class="col-sm-9 font-weight-bold">{{ format_rupiah($invoice->total) }}</dd>
            <dt class="col-sm-3">Sudah Dibayar</dt><dd class="col-sm-9 text-success">{{ format_rupiah($invoice->paidAmount()) }}</dd>
            <dt class="col-sm-3">Status</dt><dd class="col-sm-9">{{ ucfirst($invoice->status) }}</dd>
        </dl>
        <a href="{{ route('invoices.download', $invoice) }}" class="btn btn-primary"><i class="fas fa-download mr-1"></i> Unduh File</a>
    </div>
</div>

@if($invoice->payment)
    <div class="card card-outline card-info">
        <div class="card-header bg-white"><h5 class="card-title font-weight-bold mb-0">Rincian Pembayaran</h5></div>
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-3">Tanggal Bayar</dt><dd class="col-sm-9">{{ format_date($invoice->payment->paid_at) }}</dd>
                <dt class="col-sm-3">Metode</dt><dd class="col-sm-9">{{ ucfirst($invoice->payment->method) }}</dd>
                <dt class="col-sm-3">Referensi</dt><dd class="col-sm-9">{{ $invoice->payment->reference_no ?: '-' }}</dd>
                <dt class="col-sm-3">Jumlah</dt><dd class="col-sm-9 font-weight-bold">{{ format_rupiah($invoice->payment->amount) }}</dd>
            </dl>
        </div>
    </div>
@else
    <div class="card card-outline card-success">
        <div class="card-header bg-white">
            <h5 class="card-title font-weight-bold mb-0">Riwayat Pembayaran ({{ $invoice->payments->count() }} kali)</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm table-bordered mb-0">
                    <thead class="thead-light"><tr><th>No</th><th>Tanggal</th><th>Nomor Invoice</th><th>Jumlah</th><th>Metode</th><th>Referensi</th><th>Aksi</th></tr></thead>
                    <tbody>
                        @forelse($invoice->payments->sortBy('paid_at') as $payment)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ format_date($payment->paid_at) }}</td>
                                <td>{{ $payment->paymentInvoice?->number ?? '-' }}</td>
                                <td>{{ format_rupiah($payment->amount) }}</td>
                                <td>{{ ucfirst($payment->method) }}</td>
                                <td>{{ $payment->reference_no ?: '-' }}</td>
                                <td class="text-nowrap">
                                    @if($payment->paymentInvoice)
                                        <a href="{{ route('invoices.show', $payment->paymentInvoice) }}" class="btn btn-xs btn-outline-info"><i class="fas fa-eye mr-1"></i> Lihat</a>
                                        <a href="{{ route('invoices.print', $payment->paymentInvoice) }}" target="_blank" class="btn btn-xs btn-outline-secondary"><i class="fas fa-print mr-1"></i> Cetak</a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted">Belum ada pembayaran masuk.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif
@endsection
