@extends('layouts.admin')

@section('title', 'Detail Order')
@section('page_title', 'Order ORD-'.$order->id)

@section('page_actions')
    <a href="{{ route('orders.edit', $order) }}" class="btn btn-outline-secondary"><i class="fas fa-edit mr-1"></i> Ubah</a>
    <a href="{{ route('orders.index') }}" class="btn btn-default"><i class="fas fa-arrow-left mr-1"></i> Kembali</a>
@endsection

@section('content')
<div class="row">
    {{-- Ringkasan order --}}
    <div class="col-lg-8">
        <div class="card card-outline card-primary shadow-sm mb-3">
            <div class="card-header bg-white">
                <h5 class="card-title font-weight-bold mb-0">{{ $order->title }}</h5>
                <div class="card-tools">
                    <span class="badge badge-{{ $order->payment_status === 'paid' ? 'success' : 'warning' }}">
                        {{ $order->payment_status === 'paid' ? 'Lunas' : 'Belum Dibayar' }}
                    </span>
                </div>
            </div>
            <div class="card-body">
                <div class="row small">
                    <div class="col-md-6 mb-2"><span class="text-muted">Klien</span><div class="font-weight-bold">{{ $order->client?->name ?? '-' }}</div></div>
                    <div class="col-md-6 mb-2"><span class="text-muted">Platform</span><div class="font-weight-bold">{{ $order->platform?->name ?? 'Langsung' }}</div></div>
                    <div class="col-md-6 mb-2"><span class="text-muted">Paket Layanan</span><div class="font-weight-bold">{{ $order->servicePackage?->name ?? '-' }}</div></div>
                    <div class="col-md-6 mb-2"><span class="text-muted">Tanggal Order</span><div class="font-weight-bold">{{ format_date($order->ordered_at) }}</div></div>
                    <div class="col-md-6 mb-2"><span class="text-muted">Deadline</span><div class="font-weight-bold">{{ $order->deadline ? format_date($order->deadline) : '-' }}</div></div>
                    <div class="col-md-6 mb-2"><span class="text-muted">Status Pekerjaan</span><div class="font-weight-bold">{{ ucfirst(str_replace('_', ' ', $order->status)) }}</div></div>
                    @if($order->external_order_ref)
                        <div class="col-md-6 mb-2"><span class="text-muted">Referensi Eksternal</span><div class="font-weight-bold">{{ $order->external_order_ref }}</div></div>
                    @endif
                    @if($order->notes)
                        <div class="col-12 mb-2"><span class="text-muted">Catatan</span><div>{{ $order->notes }}</div></div>
                    @endif
                </div>

                <table class="table table-sm table-bordered mt-3 mb-0">
                    <thead class="thead-light"><tr><th>Rincian Potongan</th><th class="text-right" style="width:25%">Nilai</th><th class="text-right" style="width:25%">Subtotal</th></tr></thead>
                    <tbody>
                        @forelse($order->feeItems as $item)
                            <tr>
                                <td>{{ $item->label }}</td>
                                <td class="text-right">{{ $item->amount_type === 'percent' ? number_format((float) $item->amount, 2, ',', '.').'%' : format_rupiah($item->amount) }}</td>
                                <td class="text-right">{{ format_rupiah($item->subtotalFor((int) $order->gross_amount)) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-muted">Tidak ada potongan.</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr><th>Nilai Kotor</th><th></th><th class="text-right">{{ format_rupiah($order->gross_amount) }}</th></tr>
                        <tr><th>Total Potongan</th><th></th><th class="text-right">{{ format_rupiah($order->platform_fee) }}</th></tr>
                        @if((int) $order->other_fee > 0)
                            <tr><th>Biaya Lain</th><th></th><th class="text-right">{{ format_rupiah($order->other_fee) }}</th></tr>
                        @endif
                        <tr class="table-success"><th>Nilai Bersih</th><th></th><th class="text-right font-weight-bold">{{ format_rupiah($order->net_amount) }}</th></tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- Pembayaran masuk --}}
        <div class="card card-outline card-success shadow-sm mb-3">
            <div class="card-header bg-white">
                <h5 class="card-title font-weight-bold mb-0"><i class="fas fa-money-bill-wave mr-1 text-success"></i> Pembayaran Masuk</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4"><span class="text-muted small">Sudah Dibayar</span><div class="h5 mb-0 font-weight-bold text-success">{{ format_rupiah($paid) }}</div></div>
                    <div class="col-md-4"><span class="text-muted small">Sisa Tagihan</span><div class="h5 mb-0 font-weight-bold text-danger">{{ format_rupiah($outstanding) }}</div></div>
                    <div class="col-md-4"><span class="text-muted small">Total Tagihan</span><div class="h5 mb-0 font-weight-bold">{{ format_rupiah($invoice->total) }}</div></div>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-3">
                        <thead class="thead-light"><tr><th>Tanggal</th><th>Jumlah</th><th>Metode</th><th>Referensi</th><th class="text-right">Invoice</th></tr></thead>
                        <tbody>
                            @forelse($invoice->payments->sortBy('paid_at') as $payment)
                                @php $paymentInvoice = $payment->paymentInvoice; @endphp
                                <tr>
                                    <td>{{ format_date($payment->paid_at) }}</td>
                                    <td>{{ format_rupiah($payment->amount) }}</td>
                                    <td>{{ ucfirst($payment->method) }}</td>
                                    <td>{{ $payment->reference_no ?: '-' }}</td>
                                    <td class="text-right text-nowrap">
                                        @if($paymentInvoice)
                                            <a href="{{ route('invoices.show', $paymentInvoice) }}" class="btn btn-xs btn-outline-info" title="Lihat invoice pembayaran"><i class="fas fa-eye mr-1"></i> Lihat</a>
                                            <a href="{{ route('invoices.print', $paymentInvoice) }}" target="_blank" class="btn btn-xs btn-outline-secondary" title="Cetak invoice pembayaran"><i class="fas fa-print mr-1"></i> Cetak</a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted">Belum ada pembayaran masuk.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($outstanding > 0)
                    <form method="POST" action="{{ route('orders.payments.store', $order) }}">
                        @csrf
                        <div class="form-row align-items-end">
                            <div class="col-md-3 form-group mb-2">
                                <label>Tanggal Bayar *</label>
                                <input type="date" name="paid_at" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-3 form-group mb-2">
                                <label>Jumlah *</label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text">Rp</span></div>
                                    <input type="text" inputmode="numeric" autocomplete="off" name="amount" class="form-control rupiah-input" value="{{ number_format($outstanding, 0, ',', '.') }}" required>
                                </div>
                            </div>
                            <div class="col-md-2 form-group mb-2">
                                <label>Metode *</label>
                                <select name="method" class="form-control" required>
                                    <option value="transfer">Transfer</option>
                                    <option value="cash">Tunai</option>
                                    <option value="other">Lainnya</option>
                                </select>
                            </div>
                            <div class="col-md-2 form-group mb-2">
                                <label>Referensi</label>
                                <input type="text" name="reference_no" class="form-control" placeholder="Opsional">
                            </div>
                            <div class="col-md-2 form-group mb-2">
                                <button class="btn btn-success btn-block"><i class="fas fa-plus mr-1"></i> Catat</button>
                            </div>
                        </div>
                    </form>
                @else
                    <div class="callout callout-success py-2 mb-0 small"><i class="fas fa-check-circle mr-1"></i> Tagihan order ini sudah lunas.</div>
                @endif
            </div>
        </div>
    </div>

    {{-- Dokumen --}}
    <div class="col-lg-4">
        <div class="card card-outline card-info shadow-sm mb-3">
            <div class="card-header bg-white">
                <h5 class="card-title font-weight-bold mb-0"><i class="fas fa-file-invoice-dollar mr-1 text-info"></i> Invoice</h5>
            </div>
            <div class="card-body small">
                <dl class="row mb-2">
                    <dt class="col-5">Nomor</dt><dd class="col-7">{{ $invoice->number }}</dd>
                    <dt class="col-5">Terbit</dt><dd class="col-7">{{ format_date($invoice->issued_at) }}</dd>
                    <dt class="col-5">Jatuh Tempo</dt><dd class="col-7">{{ format_date($invoice->due_date) }}</dd>
                    <dt class="col-5">Total</dt><dd class="col-7 font-weight-bold">{{ format_rupiah($invoice->total) }}</dd>
                    <dt class="col-5">Status</dt><dd class="col-7">{{ ucfirst($invoice->status) }}</dd>
                </dl>
                <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-sm btn-outline-info"><i class="fas fa-eye mr-1"></i> Lihat</a>
                <a href="{{ route('invoices.print', $invoice) }}" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="fas fa-print mr-1"></i> Cetak</a>
                @if($invoice->pdf_path)
                    <a href="{{ route('invoices.download', $invoice) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-download mr-1"></i> PDF</a>
                @endif
            </div>
        </div>

        <div class="card card-outline card-secondary shadow-sm mb-3">
            <div class="card-header bg-white">
                <h5 class="card-title font-weight-bold mb-0"><i class="fas fa-file-contract mr-1 text-secondary"></i> Perjanjian Kerjasama</h5>
            </div>
            <div class="card-body small">
                @if($agreement)
                    <dl class="row mb-2">
                        <dt class="col-5">Nomor</dt><dd class="col-7">{{ $agreement->number }}</dd>
                        <dt class="col-5">Versi</dt><dd class="col-7">{{ $agreement->current_version }}</dd>
                        <dt class="col-5">Status</dt><dd class="col-7">{{ ucfirst($agreement->status) }}</dd>
                        @if($agreement->signed_at)
                            <dt class="col-5">Ditandatangani</dt><dd class="col-7">{{ format_date($agreement->signed_at) }}</dd>
                        @endif
                    </dl>
                    @php $version = $agreement->versions->sortByDesc('version_number')->first(); @endphp
                    <a href="{{ route('agreements.show', $agreement) }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-eye mr-1"></i> Lihat</a>
                    @if($version)
                        <a href="{{ route('agreement-versions.download', [$version, 'pdf']) }}" class="btn btn-sm btn-outline-primary">PDF</a>
                        <a href="{{ route('agreement-versions.download', [$version, 'docx']) }}" class="btn btn-sm btn-outline-dark">DOCX</a>
                    @endif
                @else
                    <p class="text-muted mb-0">Belum ada template perjanjian. Buat dulu di <a href="{{ route('document-templates.index') }}">Template Dokumen</a>.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
