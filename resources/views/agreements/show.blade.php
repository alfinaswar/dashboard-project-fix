@extends('layouts.admin')

@section('title', 'Detail Perjanjian')
@section('page_title', $agreement->number.' — '.$agreement->title)

@section('page_actions')
    @if($agreement->order)
        <a href="{{ route('orders.show', $agreement->order) }}" class="btn btn-default"><i class="fas fa-arrow-left mr-1"></i> Kembali ke Order</a>
    @endif
@endsection

@section('content')
<div class="card card-outline card-primary">
    <div class="card-header bg-white">
        <h3 class="card-title font-weight-bold mb-0">{{ $agreement->number }}</h3>
        <div class="card-tools"><span class="badge badge-info">{{ ucfirst($agreement->status) }}</span></div>
    </div>
    <div class="card-body">
        <dl class="row small mb-3">
            <dt class="col-sm-3">Klien</dt><dd class="col-sm-9">{{ $agreement->client?->name ?? '-' }}</dd>
            <dt class="col-sm-3">Order</dt><dd class="col-sm-9">{{ $agreement->order?->title ?? '-' }}</dd>
            <dt class="col-sm-3">Versi Aktif</dt><dd class="col-sm-9">{{ $agreement->current_version }}</dd>
            @if($agreement->signed_at)
                <dt class="col-sm-3">Ditandatangani</dt><dd class="col-sm-9">{{ format_date($agreement->signed_at) }}</dd>
            @endif
        </dl>

        @foreach($agreement->versions->sortByDesc('version_number') as $version)
            <div class="border rounded p-3 mb-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <strong>Versi {{ $version->version_number }}</strong>
                    <div>
                        @if($version->pdf_path)<a href="{{ route('agreement-versions.download', [$version, 'pdf']) }}" class="btn btn-sm btn-outline-primary">PDF</a>@endif
                        @if($version->docx_path)<a href="{{ route('agreement-versions.download', [$version, 'docx']) }}" class="btn btn-sm btn-outline-secondary">DOCX</a>@endif
                    </div>
                </div>
                <div class="p-2 bg-light small">{!! $version->body_rendered !!}</div>
            </div>
        @endforeach

        @if($agreement->status !== 'signed')
            <div class="border-top pt-3">
                <form method="POST" action="{{ route('agreements.send', $agreement) }}" class="d-inline">
                    @csrf
                    <button class="btn btn-outline-primary"><i class="fas fa-paper-plane mr-1"></i> Tandai Terkirim</button>
                </form>

                <form method="POST" enctype="multipart/form-data" action="{{ route('agreements.sign', $agreement) }}" class="mt-3">
                    @csrf
                    <div class="form-row align-items-end">
                        <div class="col-md-5 form-group">
                            <label>File Bertanda Tangan *</label>
                            <input type="file" name="signed_file" class="form-control-file" required>
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Tanggal Tanda Tangan *</label>
                            <input type="date" name="signed_at" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-2 form-group">
                            <button class="btn btn-success btn-block">Simpan</button>
                        </div>
                    </div>
                </form>
            </div>
        @endif
    </div>
</div>
@endsection
