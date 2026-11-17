@extends('layouts.admin')
@section('title',$subscription->exists?'Ubah Langganan':'Tambah Langganan') @section('page_title',$subscription->exists?'Ubah Langganan':'Tambah Langganan')

@section('content')
@php
    $billingTypes = \App\Models\ServicePackage::BILLING_TYPES;
    $durations = \App\Models\Subscription::DURATIONS;
@endphp
<form method="POST" action="{{ $subscription->exists?route('subscriptions.update',$subscription):route('subscriptions.store') }}">
    <div class="card card-primary">
        <div class="card-body">
            @csrf @if($subscription->exists)@method('PUT')@endif

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Klien *</label>
                        <select name="client_id" class="form-control select2" required>
                            <option value="">Pilih klien</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}" @selected(old('client_id',$subscription->client_id)==$client->id)>{{ $client->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Jenis Langganan *</label>
                        <select name="billing_type" id="billing_type" class="form-control" required>
                            @foreach($billingTypes as $value => $label)
                                <option value="{{ $value }}" data-months="{{ $durations[$value] ?? 12 }}" @selected(old('billing_type',$subscription->billing_type?:'annual')===$value)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Tanggal Mulai *</label>
                        <input type="date" name="start_date" id="start_date" class="form-control" value="{{ old('start_date',$subscription->start_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Tanggal Berakhir</label>
                        <input type="date" name="end_date" id="end_date" class="form-control" value="{{ old('end_date',$subscription->end_date?->format('Y-m-d')) }}">
                        <small class="form-text text-muted">Kosongkan untuk menghitung otomatis dari jenis langganan.</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Nilai Kontrak *</label>
                        <input type="text" inputmode="numeric" autocomplete="off" name="contract_value" class="form-control rupiah-input" value="{{ old('contract_value', $subscription->contract_value ?? 0) }}" required>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Status *</label>
                        <select name="status" class="form-control">
                            @foreach(['active','frozen','expired','renewed'] as $status)
                                <option value="{{ $status }}" @selected(old('status',$subscription->status?:'active')===$status)>{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="form-group">
                        <label>Alasan Pembekuan</label>
                        <input name="freeze_reason" class="form-control" value="{{ old('freeze_reason',$subscription->freeze_reason) }}">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Catatan</label>
                <textarea name="notes" class="form-control">{{ old('notes',$subscription->notes) }}</textarea>
            </div>
        </div>
        <div class="card-footer">
            <a href="{{ route('subscriptions.index') }}" class="btn btn-secondary">Batal</a>
            <button class="btn btn-primary">Simpan</button>
        </div>
    </div>
</form>
@endsection

@section('scripts')
<script>
    $(function () {
        const durations = @json($durations);

        function fillEndDate() {
            const input = $('#end_date');
            if (input.data('edited')) {
                return;
            }

            const months = durations[$('#billing_type').val()] ?? 12;
            const start = $('#start_date').val();
            if (!start) {
                return;
            }

            const date = new Date(start + 'T00:00:00');
            if (months > 0) {
                date.setMonth(date.getMonth() + months);
                date.setDate(date.getDate() - 1);
            }

            input.val(date.toISOString().slice(0, 10));
        }

        $('#end_date').on('change', function () { $(this).data('edited', true); });
        $('#billing_type, #start_date').on('change', fillEndDate);

        if (!$('#end_date').val()) {
            fillEndDate();
        }
    });
</script>
@endsection
