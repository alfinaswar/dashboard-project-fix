@extends('layouts.admin')
@section('title', 'Perjanjian Kerjasama')
@section('page_title', 'Rekap Perjanjian Kerjasama')

@section('page_actions')
    <a href="{{ route('orders.index') }}" class="btn btn-primary"><i class="fas fa-clipboard-list mr-1"></i> Kelola dari Order</a>
@endsection

@section('content')
<div class="callout callout-info py-2 small">
    <i class="fas fa-info-circle mr-1"></i> Draft PKS dibuat otomatis saat order disimpan. Tanda tangan dan unduhan diatur dari halaman detail order.
</div>
<div class="card card-outline card-primary">
    <div class="card-body">
        <table id="agreements-table" class="table table-hover w-100">
            <thead class="thead-light">
                <tr><th>Nomor</th><th>Judul</th><th>Klien</th><th>Versi</th><th>Status</th><th>Aksi</th></tr>
            </thead>
        </table>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(function () {
        $('#agreements-table').DataTable({
            processing: true, serverSide: true, ajax: '{{ route('datatable.agreements') }}', language: dtLanguage,
            columns: [
                { data: 'number', name: 'number' },
                { data: 'title', name: 'title' },
                { data: 'client_name', name: 'client.name', orderable: false },
                { data: 'current_version', name: 'current_version' },
                { data: 'status', name: 'status' },
                { data: 'actions', name: 'actions', orderable: false, searchable: false }
            ]
        });
    });
</script>
@endsection
