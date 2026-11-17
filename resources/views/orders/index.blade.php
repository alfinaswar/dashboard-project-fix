@extends('layouts.admin')
@section('title','Order Pekerjaan') @section('page_title','Order Pekerjaan')
@section('page_actions')<a href="{{ route('orders.create') }}" class="btn btn-primary"><i class="fas fa-plus mr-1"></i> Tambah Order</a>@endsection
@section('content')
<div class="card card-outline card-primary">
    <div class="card-body">
        <table id="orders-table" class="table table-hover w-100">
            <thead class="thead-light">
                <tr><th>No. Order</th><th>Klien</th><th>Platform</th><th>Nilai Kotor</th><th>Potongan</th><th>Net</th><th>Status</th><th>Pembayaran</th><th>Aksi</th></tr>
            </thead>
        </table>
    </div>
</div>
@endsection
@section('scripts')
<script>
    $(function () {
        $('#orders-table').DataTable({
            processing: true, serverSide: true, ajax: '{{ route('datatable.orders') }}', language: dtLanguage,
            columns: [
                { data: 'order_number', name: 'id' },
                { data: 'client_name', name: 'client.name', orderable: false },
                { data: 'platform_name', name: 'platform.name', orderable: false },
                { data: 'gross_amount', name: 'gross_amount' },
                { data: 'platform_fee', name: 'platform_fee' },
                { data: 'net_amount', name: 'net_amount' },
                { data: 'status', name: 'status' },
                { data: 'payment_status', name: 'id', orderable: false, searchable: false },
                { data: 'actions', name: 'actions', orderable: false, searchable: false }
            ]
        });
    });
</script>
@endsection
