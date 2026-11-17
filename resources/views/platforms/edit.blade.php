@extends('layouts.admin')

@section('title', 'Ubah Platform')
@section('page_title', 'Ubah Data Platform: ' . $platform->name)

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card card-outline card-primary shadow-sm">
            <div class="card-header bg-white">
                <h3 class="card-title font-weight-bold">Formulir Perubahan Platform</h3>
            </div>
            <form action="{{ route('platforms.update', $platform) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="alert alert-info py-2 small mb-4">
                        <i class="fas fa-info-circle mr-1"></i> Perubahan persentase komisi atau biaya platform di sini hanya akan berlaku untuk order-order baru. Data order lama tetap menggunakan nilai potongan saat order dibuat.
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="name">Nama Platform <span class="text-danger">*</span></label>
                            <input placeholder="Contoh: Fastwork" type="text" class="form-control" id="name" name="name" value="{{ old('name', $platform->name) }}" required>
                        </div>

                        <div class="col-md-6 form-group">
                            <label for="code">Kode Unik <span class="text-danger">*</span></label>
                            <input placeholder="Contoh: fastwork" type="text" class="form-control" id="code" name="code" value="{{ old('code', $platform->code) }}" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label for="commission_percent">Komisi Platform (%) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input placeholder="Contoh: 20" type="number" step="0.01" min="0" max="100" class="form-control" id="commission_percent" name="commission_percent" value="{{ old('commission_percent', $platform->commission_percent) }}" required>
                                <div class="input-group-append">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4 form-group">
                            <label for="fixed_fee">Biaya Tetap per Order (Rp) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">Rp</span>
                                </div>
                                <input placeholder="Isi 0 jika tidak ada" type="text" inputmode="numeric" autocomplete="off" class="form-control rupiah-input" id="fixed_fee" name="fixed_fee" value="{{ old('fixed_fee', $platform->fixed_fee) }}" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="notes">Catatan Tambahan</label>
                        <textarea placeholder="Informasi tambahan mengenai platform ini" class="form-control" id="notes" name="notes" rows="3">{{ old('notes', $platform->notes) }}</textarea>
                    </div>

                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" {{ old('is_active', $platform->is_active) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="is_active">Platform Aktif</label>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white">
                    <button type="submit" class="btn btn-primary font-weight-bold px-4">
                        <i class="fas fa-save mr-1"></i> Perbarui Platform
                    </button>
                    <a href="{{ route('platforms.index') }}" class="btn btn-default ml-2">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
