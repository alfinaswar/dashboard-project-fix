@extends('layouts.admin')
@section('title', 'Invoice')
@section('page_title', 'Rekap Invoice')

@section('page_actions')
    <a href="{{ route('orders.index') }}" class="btn btn-primary"><i class="fas fa-clipboard-list mr-1"></i> Kelola dari Order</a>
@endsection

@section('content')
<div class="callout callout-info py-2 small">
    <i class="fas fa-info-circle mr-1"></i> Invoice dibuat otomatis saat order disimpan. Buka order untuk melihat atau mengunduh file invoice.
</div>
<div class="card card-outline card-primary">
    <div class="card-body">
        <table id="invoices-table" class="table table-hover w-100">
            <thead class="thead-light">
                <tr><th>Nomor</th><th>Klien</th><th>Order</th><th>Terbit</th><th>Jatuh Tempo</th><th>Total</th><th>Status</th><th>Aksi</th></tr>
            </thead>
        </table>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(function () {
        $('#invoices-table').DataTable({
            processing: true, serverSide: true, ajax: '{{ route('datatable.invoices') }}', language: dtLanguage,
            columns: [
                { data: 'number', name: 'number' },
                { data: 'client_name', name: 'order.client.name', orderable: false },
                { data: 'order_title', name: 'order.title', orderable: false },
                { data: 'issued_at', name: 'issued_at' },
                { data: 'due_date', name: 'due_date' },
                { data: 'total', name: 'total' },
                { data: 'status', name: 'status' },
                { data: 'actions', name: 'actions', orderable: false, searchable: false }
            ]
        });
    });
</script>
@endsection
