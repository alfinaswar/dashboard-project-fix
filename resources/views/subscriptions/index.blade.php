@extends('layouts.admin')

@section('title', 'Langganan & Masa Aktif')
@section('page_title', 'Langganan & Masa Aktif')

@section('page_actions')
<a href="{{ route('subscriptions.create') }}" class="btn btn-primary">
    <i class="fas fa-plus mr-1"></i> Tambah Langganan
</a>
@endsection

@section('content')
<div class="card card-outline card-primary">
    <div class="card-body">
        <div class="table-responsive">
            <table id="subscriptions-table" class="table table-hover w-100">
                <thead class="thead-light">
                    <tr>
                        <th style="min-width: 180px;">Order</th>
                        <th>Klien</th>
                        <th>Jenis Langganan</th>
                        <th>Mulai</th>
                        <th>Berakhir</th>
                        <th>Nilai Kontrak</th>
                        <th>Status</th>
                        <th style="width: 120px;">Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(function () {
        $('#subscriptions-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route('datatable.subscriptions') }}',
            language: dtLanguage,
            order: [[3, 'desc']],
            columns: [
                { data: 'order_title', name: 'order.title', orderable: false },
                { data: 'client_name', name: 'client.name', orderable: false },
                { data: 'billing_type_label', name: 'billing_type', orderable: false },
                { data: 'start_date', name: 'start_date' },
                { data: 'end_date', name: 'end_date' },
                { data: 'contract_value', name: 'contract_value' },
                { data: 'status', name: 'status', orderable: false },
                { data: 'actions', name: 'actions', orderable: false, searchable: false }
            ]
        });
    });
</script>
@endsection
