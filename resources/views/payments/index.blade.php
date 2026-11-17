@extends('layouts.admin')
@section('title', 'Pembayaran Masuk')
@section('page_title', 'Semua Pembayaran Masuk')

@section('content')
<div class="callout callout-info py-2 small">
    <i class="fas fa-info-circle mr-1"></i> Pembayaran dicatat dari halaman detail order. Tabel ini hanya rekap semua pembayaran yang sudah masuk.
</div>
<div class="card card-outline card-primary">
    <div class="card-body">
        <table id="payments-table" class="table table-hover w-100">
            <thead class="thead-light">
                <tr><th>Tanggal</th><th>Invoice</th><th>Order</th><th>Klien</th><th>Jumlah</th><th>Metode</th><th>Referensi</th></tr>
            </thead>
        </table>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(function () {
        $('#payments-table').DataTable({
            processing: true, serverSide: true, ajax: '{{ route('datatable.payments') }}', language: dtLanguage,
            columns: [
                { data: 'paid_at', name: 'paid_at' },
                { data: 'invoice_number', name: 'invoice.number', orderable: false },
                { data: 'order_number', name: 'invoice.order.title', orderable: false },
                { data: 'client_name', name: 'invoice.order.client.name', orderable: false },
                { data: 'amount', name: 'amount' },
                { data: 'method', name: 'method' },
                { data: 'reference_no', name: 'reference_no' }
            ]
        });
    });
</script>
@endsection
