@extends('layouts.admin')

@section('title', 'Pengaturan Usaha')
@section('page_title', 'Pengaturan Usaha & Dokumen')

@section('content')
<form action="{{ route('settings.update') }}" method="POST">
    @csrf
    @method('PUT')

    <div class="row">
        <!-- Identitas Usaha -->
        <div class="col-lg-6">
            <div class="card card-outline card-primary shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title font-weight-bold mb-0">
                        <i class="fas fa-building mr-2 text-primary"></i> Identitas Usaha
                    </h5>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="company_name">Nama Usaha / Bisnis <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('company_name') is-invalid @enderror" id="company_name" name="company_name" value="{{ old('company_name', $settings['company_name']) }}" required>
                        @error('company_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="company_address">Alamat Lengkap</label>
                        <textarea class="form-control @error('company_address') is-invalid @enderror" id="company_address" name="company_address" rows="3">{{ old('company_address', $settings['company_address']) }}</textarea>
                        @error('company_address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="company_phone">Nomor Telepon / WA</label>
                            <input type="text" class="form-control @error('company_phone') is-invalid @enderror" id="company_phone" name="company_phone" value="{{ old('company_phone', $settings['company_phone']) }}">
                            @error('company_phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="company_email">Email Usaha</label>
                            <input type="email" class="form-control @error('company_email') is-invalid @enderror" id="company_email" name="company_email" value="{{ old('company_email', $settings['company_email']) }}">
                            @error('company_email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="company_npwp">NPWP (Opsional)</label>
                            <input type="text" class="form-control @error('company_npwp') is-invalid @enderror" id="company_npwp" name="company_npwp" value="{{ old('company_npwp', $settings['company_npwp']) }}">
                            @error('company_npwp')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="signee_name">Nama Penandatangan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('signee_name') is-invalid @enderror" id="signee_name" name="signee_name" value="{{ old('signee_name', $settings['signee_name']) }}" required>
                            <small class="form-text text-muted">Dicantumkan pada draft invoice & perjanjian.</small>
                            @error('signee_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rekening Bank & Dokumen -->
        <div class="col-lg-6">
            <div class="card card-outline card-success shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title font-weight-bold mb-0">
                        <i class="fas fa-university mr-2 text-success"></i> Rekening Bank Pembayaran
                    </h5>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="bank_name">Nama Bank <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('bank_name') is-invalid @enderror" id="bank_name" name="bank_name" value="{{ old('bank_name', $settings['bank_name']) }}" placeholder="Contoh: BCA, Mandiri" required>
                        @error('bank_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="bank_account_number">Nomor Rekening <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('bank_account_number') is-invalid @enderror" id="bank_account_number" name="bank_account_number" value="{{ old('bank_account_number', $settings['bank_account_number']) }}" required>
                        @error('bank_account_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="bank_account_name">Atas Nama Rekening <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('bank_account_name') is-invalid @enderror" id="bank_account_name" name="bank_account_name" value="{{ old('bank_account_name', $settings['bank_account_name']) }}" required>
                        @error('bank_account_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="card card-outline card-secondary shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title font-weight-bold mb-0">
                        <i class="fas fa-file-alt mr-2 text-secondary"></i> Default Dokumen & Jatuh Tempo
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="invoice_due_days">Jatuh Tempo Invoice (Hari) <span class="text-danger">*</span></label>
                            <input type="number" min="1" class="form-control @error('invoice_due_days') is-invalid @enderror" id="invoice_due_days" name="invoice_due_days" value="{{ old('invoice_due_days', $settings['invoice_due_days']) }}" required>
                            <small class="form-text text-muted">Default: 14 hari.</small>
                            @error('invoice_due_days')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="revision_days">Masa Garansi Revisi (Hari) <span class="text-danger">*</span></label>
                            <input type="number" min="0" class="form-control @error('revision_days') is-invalid @enderror" id="revision_days" name="revision_days" value="{{ old('revision_days', $settings['revision_days']) }}" required>
                            <small class="form-text text-muted">Default: 14 hari.</small>
                            @error('revision_days')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="invoice_number_format">Format Nomor Invoice <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('invoice_number_format') is-invalid @enderror" id="invoice_number_format" name="invoice_number_format" value="{{ old('invoice_number_format', $settings['invoice_number_format']) }}" required>
                        <small class="form-text text-muted">Placeholder: {YYYY}, {MM}, {NUMBER}. Contoh: INV/{YYYY}/{MM}/{NUMBER}</small>
                        @error('invoice_number_format')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="agreement_number_format">Format Nomor Perjanjian (PKS) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('agreement_number_format') is-invalid @enderror" id="agreement_number_format" name="agreement_number_format" value="{{ old('agreement_number_format', $settings['agreement_number_format']) }}" required>
                        <small class="form-text text-muted">Placeholder: {YYYY}, {NUMBER}. Contoh: PKS/{YYYY}/{NUMBER}</small>
                        @error('agreement_number_format')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-5">
        <div class="col-12">
            <button type="submit" class="btn btn-primary font-weight-bold px-4">
                <i class="fas fa-save mr-1"></i> Simpan Pengaturan
            </button>
            <a href="{{ route('dashboard') }}" class="btn btn-default ml-2">Batal</a>
        </div>
    </div>
</form>
@endsection
