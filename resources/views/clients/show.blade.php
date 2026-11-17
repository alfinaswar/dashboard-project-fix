@extends('layouts.admin')
@section('title', 'Detail Klien')
@section('page_title', 'Detail Klien')
@section('page_actions')<a href="{{ route('clients.edit', $client) }}" class="btn btn-primary"><i class="fas fa-edit mr-1"></i> Ubah</a>@endsection
@section('content')<div class="card card-outline card-primary"><div class="card-body"><h3>{{ $client->name }}</h3><dl class="row mb-0"><dt class="col-sm-3">Perusahaan</dt><dd class="col-sm-9">{{ $client->company_name ?: '-' }}</dd><dt class="col-sm-3">Kontak</dt><dd class="col-sm-9">{{ $client->contact_name ?: '-' }}</dd><dt class="col-sm-3">Email</dt><dd class="col-sm-9">{{ $client->email ?: '-' }}</dd><dt class="col-sm-3">Telepon</dt><dd class="col-sm-9">{{ $client->phone ?: '-' }}</dd><dt class="col-sm-3">Alamat</dt><dd class="col-sm-9">{{ $client->address ?: '-' }}</dd></dl></div></div>@endsection
