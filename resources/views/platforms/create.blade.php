@extends('layouts.admin')

@section('title', 'Tambah Platform')
@section('page_title', 'Tambah Platform Baru')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card card-outline card-primary shadow-sm">
            <div class="card-header bg-white">
                <h3 class="card-title font-weight-bold">Formulir Platform</h3>
            </div>
            <form action="{{ route('platforms.store') }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="name">Nama Platform <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" placeholder="Contoh: Fastwork, Sribulancer" required>
                        </div>

                        <div class="col-md-6 form-group">
                            <label for="code">Kode Unik <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="code" name="code" value="{{ old('code') }}" placeholder="Contoh: fastwork, direct" required>
                            <small class="form-text text-muted">Huruf kecil, angka, atau tanda hubung (-). Unik di sistem.</small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label for="commission_percent">Komisi Platform (%) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input placeholder="Contoh: 20" type="number" step="0.01" min="0" max="100" class="form-control" id="commission_percent" name="commission_percent" value="{{ old('commission_percent', '0.00') }}" required>
                                <div class="input-group-append">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                            <small class="form-text text-muted">Fastwork default: 20%.</small>
                        </div>

                        <div class="col-md-4 form-group">
                            <label for="fixed_fee">Biaya Tetap per Order (Rp) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">Rp</span>
                                </div>
                                <input placeholder="Isi 0 jika tidak ada" type="text" inputmode="numeric" autocomplete="off" class="form-control rupiah-input" id="fixed_fee" name="fixed_fee" value="{{ old('fixed_fee', '0') }}" required>
                            </div>
                            <small class="form-text text-muted">Isi 0 jika tidak ada biaya tetap.</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="notes">Catatan Tambahan</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Informasi tambahan mengenai platform ini">{{ old('notes') }}</textarea>
                    </div>

                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="is_active">Platform Aktif</label>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white">
                    <button type="submit" class="btn btn-primary font-weight-bold px-4">
                        <i class="fas fa-save mr-1"></i> Simpan Platform
                    </button>
                    <a href="{{ route('platforms.index') }}" class="btn btn-default ml-2">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
