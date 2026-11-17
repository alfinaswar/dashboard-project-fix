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
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" placeholder="Contoh: Fastwork, Sribulancer" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 form-group">
                            <label for="code">Kode Unik <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('code') is-invalid @enderror" id="code" name="code" value="{{ old('code') }}" placeholder="Contoh: fastwork, direct" required>
                            <small class="form-text text-muted">Huruf kecil, angka, atau tanda hubung (-). Unik di sistem.</small>
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label for="commission_percent">Komisi Platform (%) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input placeholder="Masukkan commission percent" type="number" step="0.01" min="0" max="100" class="form-control @error('commission_percent') is-invalid @enderror" id="commission_percent" name="commission_percent" value="{{ old('commission_percent', '0.00') }}" required>
                                <div class="input-group-append">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                            <small class="form-text text-muted">Fastwork default: 20%.</small>
                            @error('commission_percent')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 form-group">
                            <label for="fixed_fee">Biaya Tetap per Order (Rp) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">Rp</span>
                                </div>
                                <input placeholder="Masukkan fixed fee" type="number" min="0" class="form-control @error('fixed_fee') is-invalid @enderror" id="fixed_fee" name="fixed_fee" value="{{ old('fixed_fee', '0') }}" required>
                            </div>
                            <small class="form-text text-muted">Isi 0 jika tidak ada biaya tetap.</small>
                            @error('fixed_fee')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 form-group">
                            <label for="withdrawal_fee">Biaya Penarikan Saldo (Rp) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">Rp</span>
                                </div>
                                <input placeholder="Masukkan withdrawal fee" type="number" min="0" class="form-control @error('withdrawal_fee') is-invalid @enderror" id="withdrawal_fee" name="withdrawal_fee" value="{{ old('withdrawal_fee', '0') }}" required>
                            </div>
                            <small class="form-text text-muted">Biaya transfer bank saat menarik dana.</small>
                            @error('withdrawal_fee')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="notes">Catatan Tambahan</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3" placeholder="Informasi tambahan mengenai platform ini">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
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
