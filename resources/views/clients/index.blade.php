@extends('layouts.admin')
@section('title', 'Klien')
@section('page_title', 'Master Klien')
@section('page_actions')<a href="{{ route('clients.create') }}" class="btn btn-primary"><i class="fas fa-plus mr-1"></i> Tambah Klien</a>@endsection
@section('content')<div class="card card-outline card-primary"><div class="card-body"><table id="clients-table" class="table table-hover w-100"><thead class="thead-light"><tr><th>Nama</th><th>Kontak</th><th>Platform Utama</th><th>Email</th><th>Aksi</th></tr></thead></table></div></div>@endsection
@section('scripts')<script>$(function(){ $('#clients-table').DataTable({processing:true,serverSide:true,ajax:'{{ route('datatable.clients') }}',language:dtLanguage,columns:[{data:'name',name:'name'},{data:'contact_name',name:'contact_name'},{data:'platform_name',name:'platform.name',orderable:false},{data:'email',name:'email'},{data:'actions',name:'actions',orderable:false,searchable:false}]}); });</script>@endsection
