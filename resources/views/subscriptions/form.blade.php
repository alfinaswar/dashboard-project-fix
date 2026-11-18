@extends('layouts.admin')

@section('title', $subscription->exists ? 'Ubah Langganan' : 'Tambah Langganan')
@section('page_title', $subscription->exists ? 'Ubah Langganan' : 'Tambah Langganan')

@section('content')
@php
    $billingTypes = \App\Models\ServicePackage::BILLING_TYPES;
    $durations = \App\Models\Subscription::DURATIONS;
    $selectedOrderId = old('order_id', $subscription->order_id);
    $selectedOrder = $orders->firstWhere('id', $selectedOrderId);
    $initialClientName = $selectedOrder?->client?->name ?? $subscription->client?->name ?? '';
@endphp

<form method="POST" action="{{ $subscription->exists ? route('subscriptions.update', $subscription) : route('subscriptions.store') }}">
    @csrf
    @if($subscription->exists)
        @method('PUT')
    @endif

    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header">
            <h3 class="card-title font-weight-bold">
                <i class="fas fa-clock mr-1 text-primary"></i> Formulir Langganan Berdasarkan Order
            </h3>
        </div>
        <div class="card-body">

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <h5 class="font-weight-bold"><i class="icon fas fa-ban"></i> Terjadi Kesalahan</h5>
                    <ul class="mb-0 pl-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <div class="row">
                <!-- Order Pekerjaan -->
                <div class="col-md-7">
                    <div class="form-group">
                        <label for="order_id" class="font-weight-bold">Order Pekerjaan <span class="text-danger">*</span></label>
                        <select name="order_id" id="order_id" class="form-control select2 @error('order_id') is-invalid @enderror" data-placeholder="Pilih order pekerjaan..." required>
                            <option value="">-- Pilih Order Pekerjaan --</option>
                            @foreach($orders as $order)
                                @php
                                    $platformName = $order->platform?->name ?? 'Langsung';
                                    $platformCode = strtolower($order->platform?->code ?? $platformName);
                                    $isFastwork = str_contains($platformCode, 'fastwork');
                                    $contractVal = $isFastwork ? $order->gross_amount : $order->net_amount;
                                @endphp
                                <option 
                                    value="{{ $order->id }}"
                                    data-client-id="{{ $order->client_id }}"
                                    data-client-name="{{ $order->client?->name ?? '-' }}"
                                    data-platform="{{ $platformName }}"
                                    data-is-fastwork="{{ $isFastwork ? '1' : '0' }}"
                                    data-gross="{{ $order->gross_amount }}"
                                    data-net="{{ $order->net_amount }}"
                                    data-calculated-val="{{ $contractVal }}"
                                    data-billing-type="{{ $order->billing_type }}"
                                    @selected($selectedOrderId == $order->id)
                                >
                                    [ORD-{{ $order->id }}] {{ $order->title }} &mdash; {{ $order->client?->name ?? 'Tanpa Klien' }} ({{ $platformName }})
                                </option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">Langganan terikat langsung dengan data order pekerjaan.</small>
                        @error('order_id')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Klien Terkait (Otomatis dari Order) -->
                <div class="col-md-5">
                    <div class="form-group">
                        <label for="client_name_display" class="font-weight-bold">Klien Terkait</label>
                        <input 
                            type="text" 
                            id="client_name_display" 
                            class="form-control bg-light" 
                            value="{{ $initialClientName }}" 
                            readonly 
                            placeholder="Nama klien otomatis terisi setelah memilih order..."
                        >
                        <input 
                            type="hidden" 
                            name="client_id" 
                            id="client_id" 
                            value="{{ old('client_id', $subscription->client_id ?? $selectedOrder?->client_id) }}"
                        >
                        <small class="form-text text-muted">Ditentukan otomatis berdasarkan klien pada order pekerjaan.</small>
                    </div>
                </div>
            </div>

            <!-- Box Info Perhitungan Nilai Kontrak Berdasarkan Platform -->
            <div id="order_pricing_info" class="alert alert-info border-0 p-3 mb-4" style="display: none; background-color: #f0f7ff; border-left: 4px solid #3b82f6 !important; color: #1e3a8a;">
                <div class="d-flex align-items-center">
                    <i class="fas fa-info-circle fa-2x mr-3 text-primary"></i>
                    <div>
                        <div class="font-weight-bold" id="pricing_rule_title">Aturan Nilai Kontrak</div>
                        <div id="pricing_rule_desc" class="small mt-1 text-muted"></div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Jenis Langganan -->
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="billing_type" class="font-weight-bold">Jenis Langganan <span class="text-danger">*</span></label>
                        <select name="billing_type" id="billing_type" class="form-control @error('billing_type') is-invalid @enderror" required>
                            <option value="">-- Pilih Jenis Langganan --</option>
                            @foreach($billingTypes as $value => $label)
                                <option 
                                    value="{{ $value }}" 
                                    data-months="{{ $durations[$value] ?? 12 }}" 
                                    @selected(old('billing_type', $subscription->billing_type ?: 'annual') === $value)
                                >
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">Menentukan masa aktif (Tahunan: 12 bln, Bulanan: 1 bln).</small>
                        @error('billing_type')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Tanggal Mulai -->
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="start_date" class="font-weight-bold">Tanggal Mulai <span class="text-danger">*</span></label>
                        <input 
                            type="date" 
                            name="start_date" 
                            id="start_date" 
                            class="form-control @error('start_date') is-invalid @enderror" 
                            value="{{ old('start_date', $subscription->start_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" 
                            placeholder="YYYY-MM-DD (Pilih tanggal mulai)"
                            required
                        >
                        <small class="form-text text-muted">Awal berlakunya masa aktif langganan.</small>
                        @error('start_date')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Tanggal Berakhir -->
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="end_date" class="font-weight-bold">Tanggal Berakhir</label>
                        <input 
                            type="date" 
                            name="end_date" 
                            id="end_date" 
                            class="form-control @error('end_date') is-invalid @enderror" 
                            value="{{ old('end_date', $subscription->end_date?->format('Y-m-d')) }}"
                            placeholder="YYYY-MM-DD (Otomatis dari durasi jika dikosongkan)"
                        >
                        <small class="form-text text-muted">Kosongkan untuk menghitung otomatis dari jenis langganan.</small>
                        @error('end_date')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Nilai Kontrak -->
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="contract_value" class="font-weight-bold">
                            Nilai Kontrak <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text font-weight-bold">Rp</span>
                            </div>
                            <input 
                                type="text" 
                                inputmode="numeric" 
                                autocomplete="off" 
                                name="contract_value" 
                                id="contract_value" 
                                class="form-control rupiah-input @error('contract_value') is-invalid @enderror" 
                                value="{{ old('contract_value', $subscription->contract_value ?? 0) }}" 
                                placeholder="Masukkan nilai kontrak (Rp)..."
                                required
                            >
                        </div>
                        <small id="contract_value_note" class="form-text text-muted">
                            Fastwork: mengambil Nilai Kotor &bull; Langsung: mengambil Nilai Bersih.
                        </small>
                        @error('contract_value')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Status Langganan -->
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="status" class="font-weight-bold">Status <span class="text-danger">*</span></label>
                        <select name="status" id="status" class="form-control @error('status') is-invalid @enderror" required>
                            <option value="">-- Pilih Status Langganan --</option>
                            @foreach(['active' => 'Active (Aktif)', 'frozen' => 'Frozen (Dibekukan)', 'expired' => 'Expired (Kedaluwarsa)', 'renewed' => 'Renewed (Diperpanjang)'] as $statusKey => $statusLabel)
                                <option value="{{ $statusKey }}" @selected(old('status', $subscription->status ?: 'active') === $statusKey)>
                                    {{ $statusLabel }}
                                </option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">Status langganan saat ini.</small>
                        @error('status')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Alasan Pembekuan (Hanya jika status Frozen) -->
            <div class="form-group" id="freeze_reason_group">
                <label for="freeze_reason" class="font-weight-bold">
                    Alasan Pembekuan <span class="text-danger" id="freeze_reason_star" style="display: none;">*</span>
                </label>
                <input 
                    type="text" 
                    name="freeze_reason" 
                    id="freeze_reason" 
                    class="form-control @error('freeze_reason') is-invalid @enderror" 
                    value="{{ old('freeze_reason', $subscription->freeze_reason) }}"
                    placeholder="Masukkan alasan mengapa langganan dibekukan (wajib diisi jika status dibekukan)..."
                >
                <small class="form-text text-muted">Diperlukan apabila status diubah menjadi Frozen (Dibekukan).</small>
                @error('freeze_reason')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <!-- Catatan -->
            <div class="form-group">
                <label for="notes" class="font-weight-bold">Catatan Tambahan</label>
                <textarea 
                    name="notes" 
                    id="notes" 
                    rows="3" 
                    class="form-control @error('notes') is-invalid @enderror" 
                    placeholder="Tambahkan catatan khusus, nomor referensi kontrak, atau kesepakatan spesifik..."
                >{{ old('notes', $subscription->notes) }}</textarea>
                <small class="form-text text-muted">Catatan internal terkait masa aktif atau kesepakatan order ini.</small>
                @error('notes')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

        </div>
        <div class="card-footer bg-light d-flex justify-content-between">
            <a href="{{ route('subscriptions.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Batal
            </a>
            <button type="submit" class="btn btn-primary font-weight-bold">
                <i class="fas fa-save mr-1"></i> {{ $subscription->exists ? 'Simpan Perubahan' : 'Buat Langganan' }}
            </button>
        </div>
    </div>
</form>
@endsection

@section('scripts')
<script>
    $(function () {
        const durations = @json($durations);

        // Helper format rupiah untuk angka
        function formatRupiahNumber(number) {
            if (isNaN(number) || number === null || number === '') return '0';
            return parseInt(number, 10).toLocaleString('id-ID');
        }

        // 1. Logika Pemilihan Order & Otomasi Nilai Kontrak
        $('#order_id').on('change', function () {
            const selected = $(this).find('option:selected');
            const clientId = selected.data('client-id');
            const clientName = selected.data('client-name');
            const platform = selected.data('platform') || '';
            const isFastwork = selected.data('is-fastwork') === 1 || selected.data('is-fastwork') === '1';
            const gross = selected.data('gross') || 0;
            const net = selected.data('net') || 0;
            const calculatedVal = selected.data('calculated-val');
            const billingType = selected.data('billing-type');

            if ($(this).val()) {
                // Set Client display & hidden input
                $('#client_name_display').val(clientName);
                $('#client_id').val(clientId);

                // Auto set billing_type jika cocok
                if (billingType && $('#billing_type option[value="' + billingType + '"]').length > 0) {
                    $('#billing_type').val(billingType).trigger('change');
                }

                // Hitung & isi nilai kontrak:
                // Fastwork -> Nilai Kotor (Gross)
                // Langsung / Lainnya -> Nilai Bersih (Net)
                const valToSet = isFastwork ? gross : net;
                $('#contract_value').val(formatRupiahNumber(valToSet));

                // Tampilkan info card penjelasan
                $('#order_pricing_info').slideDown(200);
                if (isFastwork) {
                    $('#pricing_rule_title').html('<span class="badge badge-warning mr-2">Platform Fastwork</span> Nilai Kontrak Diambil dari Nilai Kotor (Gross)');
                    $('#pricing_rule_desc').html('Order ini berasal dari Fastwork. Nilai kontrak otomatis disetel sebesar <strong>Rp ' + formatRupiahNumber(gross) + '</strong> (Nilai Kotor sebelum potongan komisi platform).');
                    $('#contract_value_note').html('<span class="text-primary font-weight-bold"><i class="fas fa-check-circle"></i> Nilai Kotor Fastwork: Rp ' + formatRupiahNumber(gross) + '</span> (Nilai Bersih: Rp ' + formatRupiahNumber(net) + ')');
                } else {
                    $('#pricing_rule_title').html('<span class="badge badge-success mr-2">Platform Langsung</span> Nilai Kontrak Diambil dari Nilai Bersih (Net)');
                    $('#pricing_rule_desc').html('Order ini berasal dari klien langsung (di luar Fastwork). Nilai kontrak otomatis disetel sebesar <strong>Rp ' + formatRupiahNumber(net) + '</strong> (Nilai Bersih transaksi).');
                    $('#contract_value_note').html('<span class="text-success font-weight-bold"><i class="fas fa-check-circle"></i> Nilai Bersih Langsung: Rp ' + formatRupiahNumber(net) + '</span> (Nilai Kotor: Rp ' + formatRupiahNumber(gross) + ')');
                }
            } else {
                $('#client_name_display').val('');
                $('#client_id').val('');
                $('#order_pricing_info').slideUp(200);
                $('#contract_value_note').text('Fastwork: mengambil Nilai Kotor • Langsung: mengambil Nilai Bersih.');
            }
        });

        // Trigger change pada saat load jika sudah ada order yang terpilih (misal saat edit atau validasi error)
        if ($('#order_id').val()) {
            const selected = $('#order_id').find('option:selected');
            const platform = selected.data('platform') || '';
            const isFastwork = selected.data('is-fastwork') === 1 || selected.data('is-fastwork') === '1';
            const gross = selected.data('gross') || 0;
            const net = selected.data('net') || 0;

            $('#order_pricing_info').show();
            if (isFastwork) {
                $('#pricing_rule_title').html('<span class="badge badge-warning mr-2">Platform Fastwork</span> Nilai Kontrak Diambil dari Nilai Kotor (Gross)');
                $('#pricing_rule_desc').html('Order ini berasal dari Fastwork. Nilai kontrak disetel sebesar <strong>Rp ' + formatRupiahNumber(gross) + '</strong> (Nilai Kotor).');
            } else {
                $('#pricing_rule_title').html('<span class="badge badge-success mr-2">Platform Langsung</span> Nilai Kontrak Diambil dari Nilai Bersih (Net)');
                $('#pricing_rule_desc').html('Order ini berasal dari klien langsung. Nilai kontrak disetel sebesar <strong>Rp ' + formatRupiahNumber(net) + '</strong> (Nilai Bersih).');
            }
        }

        // 2. Perhitungan Tanggal Berakhir Otomatis
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

        $('#end_date').on('change', function () { 
            $(this).data('edited', true); 
        });

        $('#billing_type, #start_date').on('change', fillEndDate);

        if (!$('#end_date').val()) {
            fillEndDate();
        }

        // 3. Conditional Required untuk Alasan Pembekuan
        function toggleFreezeReason() {
            const status = $('#status').val();
            if (status === 'frozen') {
                $('#freeze_reason_star').show();
                $('#freeze_reason').attr('required', true);
            } else {
                $('#freeze_reason_star').hide();
                $('#freeze_reason').removeAttr('required');
            }
        }

        $('#status').on('change', toggleFreezeReason);
        toggleFreezeReason();
    });
</script>
@endsection
