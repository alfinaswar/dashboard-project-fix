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
@php
    $payments = $invoice->payment ? collect() : $invoice->payments->sortBy('paid_at');
    $paid = $invoice->paidAmount();
@endphp

<div class="card card-outline card-primary">
    <div class="card-body">
        {{-- Kepala: identitas perusahaan dan nomor dokumen --}}
        <div class="d-flex justify-content-between border-bottom pb-3 mb-3">
            <div>
                <h4 class="mb-0 font-weight-bold">{{ setting('company_name', 'Studio Freelance') }}</h4>
                <div class="small text-muted">{!! nl2br(e(setting('company_address', ''))) !!}</div>
            </div>
            <div class="text-right">
                <h5 class="mb-1 font-weight-bold">{{ $invoice->payment ? 'BUKTI PEMBAYARAN' : 'INVOICE' }}</h5>
                <div class="small">Nomor: <strong>{{ $invoice->number }}</strong></div>
                <div class="small">Tanggal: {{ format_date($invoice->issued_at) }}</div>
                <span class="badge badge-{{ $invoice->status === 'paid' ? 'success' : ($invoice->status === 'unpaid' ? 'warning' : 'secondary') }} mt-1">
                    {{ ucfirst($invoice->status) }}
                </span>
            </div>
        </div>

        {{-- Kepada dan pekerjaan --}}
        <div class="row mb-3">
            <div class="col-sm-6">
                <div class="small text-muted">Kepada</div>
                <div class="font-weight-bold">{{ $invoice->order?->client?->name ?: '-' }}</div>
                <div class="small">{{ $invoice->order?->client?->address ?: '' }}</div>
            </div>
            <div class="col-sm-6">
                <div class="small text-muted">Pekerjaan</div>
                <div class="font-weight-bold">{{ $invoice->order?->title ?: 'Layanan' }}</div>
                @if($invoice->order?->platform)
                    <div class="small">Platform: {{ $invoice->order->platform->name }}</div>
                @endif
                <div class="small">Jatuh tempo: {{ format_date($invoice->due_date) }}</div>
            </div>
        </div>

        {{-- Rincian nilai --}}
        <table class="table table-sm table-bordered mb-3">
            <thead class="thead-light"><tr><th>Keterangan</th><th class="text-right" style="width:30%">Jumlah</th></tr></thead>
            <tbody>
                <tr>
                    <td>Subtotal</td>
                    <td class="text-right">{{ format_rupiah($invoice->subtotal) }}</td>
                </tr>
                @if($invoice->tax_amount)
                    <tr>
                        <td>Pajak</td>
                        <td class="text-right">{{ format_rupiah($invoice->tax_amount) }}</td>
                    </tr>
                @endif
            </tbody>
            <tfoot>
                <tr><th>Total</th><th class="text-right">{{ format_rupiah($invoice->total) }}</th></tr>
                @unless($invoice->payment)
                    <tr><th>Sudah Dibayar</th><th class="text-right text-success">{{ format_rupiah($paid) }}</th></tr>
                    <tr><th>Sisa Tagihan</th><th class="text-right {{ $invoice->total - $paid > 0 ? 'text-danger' : 'text-success' }}">{{ format_rupiah(max(0, $invoice->total - $paid)) }}</th></tr>
                @endunless
            </tfoot>
        </table>

        @if($invoice->payment)
            <div class="small text-muted mb-3">Rincian Pembayaran</div>
            <table class="table table-sm table-bordered mb-3">
                <tbody>
                    <tr><th style="width:30%">Tanggal Bayar</th><td>{{ format_date($invoice->payment->paid_at) }}</td></tr>
                    <tr><th>Metode</th><td>{{ ucfirst($invoice->payment->method) }}</td></tr>
                    <tr><th>Referensi</th><td>{{ $invoice->payment->reference_no ?: '-' }}</td></tr>
                    <tr><th>Jumlah</th><td class="font-weight-bold">{{ format_rupiah($invoice->payment->amount) }}</td></tr>
                </tbody>
            </table>
        @endif

        <p class="small text-muted mb-3">
            Rekening: {{ setting('bank_name', '') }} {{ setting('bank_account_number', '') }} a.n. {{ setting('bank_account_name', '') }}
        </p>

        <a href="{{ route('invoices.download', $invoice) }}" class="btn btn-primary"><i class="fas fa-download mr-1"></i> Unduh File</a>
    </div>
</div>

{{-- Riwayat setoran hanya untuk invoice order; invoice pembayaran cuma satu setoran --}}
@unless($invoice->payment)
    <div class="card card-outline card-success">
        <div class="card-header bg-white">
            <h5 class="card-title font-weight-bold mb-0">Riwayat Pembayaran ({{ $payments->count() }} kali)</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm table-bordered mb-0">
                    <thead class="thead-light"><tr><th>No</th><th>Tanggal</th><th>Nomor Invoice</th><th class="text-right">Jumlah</th><th>Metode</th><th>Referensi</th><th>Aksi</th></tr></thead>
                    <tbody>
                        @forelse($payments as $payment)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ format_date($payment->paid_at) }}</td>
                                <td>{{ $payment->paymentInvoice?->number ?? '-' }}</td>
                                <td class="text-right">{{ format_rupiah($payment->amount) }}</td>
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
@endunless
@endsection
