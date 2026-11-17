@extends('layouts.admin')

@section('title', 'Detail Invoice')
@section('page_title', 'Invoice '.$invoice->number)

@section('page_actions')
    @if($invoice->order)
        <a href="{{ route('orders.show', $invoice->order) }}" class="btn btn-default"><i class="fas fa-arrow-left mr-1"></i> Kembali ke Order</a>
    @endif
@endsection

@section('content')
<div class="card card-outline card-primary">
    <div class="card-body">
        <dl class="row">
            <dt class="col-sm-3">Nomor</dt><dd class="col-sm-9">{{ $invoice->number }}</dd>
            <dt class="col-sm-3">Order</dt><dd class="col-sm-9">{{ $invoice->order?->title ?: '-' }}</dd>
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
@endsection
