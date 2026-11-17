@extends('layouts.print')@section('title','Cetak Invoice '.$invoice->number)
@php
    // Invoice pembayaran hanya bukti satu setoran; invoice order memuat riwayat semua setoran.
    $paymentInvoice = $invoice->payment;
    $order = $invoice->order;
    $payments = $order ? $order->orderInvoices()->with('payments')->get()->flatMap->payments->sortBy('paid_at') : collect();
@endphp
@section('content')
<div class="print-toolbar d-print-none">
    <button onclick="window.print()" class="btn btn-primary btn-sm"><i class="fas fa-print mr-1"></i> Cetak</button>
    <a href="{{ $order ? route('orders.show', $order) : route('invoices.index') }}" class="btn btn-default btn-sm">Kembali</a>
</div>

<div class="invoice-sheet">
    <div class="d-flex justify-content-between mb-3">
        <div>
            <h3 class="mb-0 font-weight-bold">{{ setting('company_name', 'Studio Freelance') }}</h3>
            <div class="small text-muted">{!! nl2br(e(setting('company_address', ''))) !!}</div>
        </div>
        <div class="text-right">
            <h4 class="mb-1 font-weight-bold">{{ $paymentInvoice ? 'BUKTI PEMBAYARAN' : 'INVOICE' }}</h4>
            <div class="small">Nomor: <strong>{{ $invoice->number }}</strong></div>
            <div class="small">Tanggal: {{ format_date($invoice->issued_at) }}</div>
        </div>
    </div>

    <table class="table table-sm table-borderless mb-3">
        <tr>
            <td class="pl-0" style="width:50%">
                <div class="small text-muted">Kepada</div>
                <div class="font-weight-bold">{{ $order?->client?->name ?: '-' }}</div>
                <div class="small">{{ $order?->client?->address ?: '' }}</div>
            </td>
            <td>
                <div class="small text-muted">Pekerjaan</div>
                <div class="font-weight-bold">{{ $order?->title ?: 'Layanan' }}</div>
                @if($order?->platform)
                    <div class="small">Platform: {{ $order->platform->name }}</div>
                @endif
            </td>
        </tr>
    </table>

    @if($paymentInvoice)
        <table class="table table-sm table-bordered">
            <thead class="thead-light"><tr><th>Keterangan</th><th class="text-right" style="width:30%">Jumlah</th></tr></thead>
            <tbody>
                <tr>
                    <td>
                        Pembayaran diterima {{ format_date($paymentInvoice->paid_at) }}
                        ({{ ucfirst($paymentInvoice->method) }}{{ $paymentInvoice->reference_no ? ', ref '.$paymentInvoice->reference_no : '' }})
                    </td>
                    <td class="text-right">{{ format_rupiah($invoice->total) }}</td>
                </tr>
            </tbody>
            <tfoot>
                <tr><th>Total Dibayar</th><th class="text-right">{{ format_rupiah($invoice->total) }}</th></tr>
            </tfoot>
        </table>
    @else
        <table class="table table-sm table-bordered">
            <thead class="thead-light"><tr><th>Deskripsi</th><th class="text-right" style="width:30%">Jumlah</th></tr></thead>
            <tbody>
                <tr><td>{{ $order?->title ?: 'Layanan' }}</td><td class="text-right">{{ format_rupiah($invoice->subtotal) }}</td></tr>
                <tr><td>Pajak</td><td class="text-right">{{ format_rupiah($invoice->tax_amount) }}</td></tr>
            </tbody>
            <tfoot><tr><th>Total Tagihan</th><th class="text-right">{{ format_rupiah($invoice->total) }}</th></tr></tfoot>
        </table>

        <h5 class="font-weight-bold mt-4">Riwayat Pembayaran</h5>
        <table class="table table-sm table-bordered">
            <thead class="thead-light"><tr><th>No</th><th>Tanggal</th><th>Metode</th><th>Referensi</th><th class="text-right">Jumlah</th></tr></thead>
            <tbody>
                @forelse($payments as $payment)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ format_date($payment->paid_at) }}</td>
                        <td>{{ ucfirst($payment->method) }}</td>
                        <td>{{ $payment->reference_no ?: '-' }}</td>
                        <td class="text-right">{{ format_rupiah($payment->amount) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center">Belum ada pembayaran.</td></tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr><th colspan="4">Sudah Dibayar ({{ $payments->count() }} kali)</th><th class="text-right">{{ format_rupiah($payments->sum('amount')) }}</th></tr>
                <tr><th colspan="4">Sisa Tagihan</th><th class="text-right">{{ format_rupiah(max(0, $invoice->total - $payments->sum('amount'))) }}</th></tr>
            </tfoot>
        </table>
    @endif

    <p class="small mt-4 mb-0">
        Rekening: {{ setting('bank_name', '') }} {{ setting('bank_account_number', '') }} a.n. {{ setting('bank_account_name', '') }}
    </p>
</div>
@endsection
