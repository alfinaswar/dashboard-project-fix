@extends('layouts.admin')

@section('title', 'Master Platform')
@section('page_title', 'Master Platform Sumber Order')

@section('page_actions')
<a href="{{ route('platforms.create') }}" class="btn btn-primary font-weight-bold">
    <i class="fas fa-plus mr-1"></i> Tambah Platform
</a>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card card-outline card-primary shadow-sm mb-4">
            <div class="card-header bg-white">
                <h3 class="card-title font-weight-bold">
                    <i class="fas fa-layer-group text-primary mr-1"></i> Daftar Platform
                </h3>
            </div>
            <div class="card-body">
                <table id="platforms-table" class="table table-hover table-striped align-middle w-100">
                    <thead class="thead-light">
                        <tr>
                            <th>Nama Platform</th>
                            <th>Kode</th>
                            <th>Komisi (%)</th>
                            <th>Biaya Tetap</th>
                            <th>Status</th>
                            <th>Klien</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <!-- Simulator Kalkulator Potongan -->
    <div class="col-lg-4">
        <div class="card card-outline card-success shadow-sm mb-4">
            <div class="card-header bg-white">
                <h3 class="card-title font-weight-bold">
                    <i class="fas fa-calculator text-success mr-1"></i> Simulasi Potongan Platform
                </h3>
            </div>
            <div class="card-body">
                <p class="text-muted small">
                    Uji rumus potongan: <br><code>platform_fee = round(gross * komisi / 100) + fixed_fee</code>
                </p>

                <div class="form-group">
                    <label for="sim_platform">Pilih Platform</label>
                    <select id="sim_platform" class="form-control">
                        @foreach($platforms as $p)
                            <option value="{{ $p->id }}" data-commission="{{ $p->commission_percent }}" data-fixed="{{ $p->fixed_fee }}">
                                {{ $p->name }} ({{ number_format($p->commission_percent, 2) }}% + {{ format_rupiah($p->fixed_fee) }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="sim_gross">Nilai Kotor / Gross (Rp)</label>
                    <input placeholder="Masukkan nilai kotor" type="text" inputmode="numeric" autocomplete="off" id="sim_gross" class="form-control rupiah-input" value="1.000.000">
                </div>

                <div class="form-group">
                    <label for="sim_other">Biaya Lain / Extra (Rp)</label>
                    <input placeholder="Masukkan biaya lain" type="text" inputmode="numeric" autocomplete="off" id="sim_other" class="form-control rupiah-input" value="0">
                </div>

                <div class="p-3 bg-light rounded border">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Potongan Platform:</span>
                        <strong id="res_fee" class="text-danger">Rp 0</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Biaya Lain:</span>
                        <strong id="res_other" class="text-warning">Rp 0</strong>
                    </div>
                    <hr class="my-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="font-weight-bold">Nilai Bersih (Net):</span>
                        <h4 id="res_net" class="text-success font-weight-bold mb-0">Rp 0</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(function() {
        $('#platforms-table').DataTable({
            processing: true, serverSide: true, ajax: '{{ route('datatable.platforms') }}', language: dtLanguage,
            columns: [
                { data: 'name', name: 'name' },
                { data: 'code', name: 'code' },
                { data: 'commission_percent', name: 'commission_percent' },
                { data: 'fixed_fee', name: 'fixed_fee' },
                { data: 'is_active', name: 'is_active' },
                { data: 'clients_count', name: 'clients_count', orderable: false, searchable: false },
                { data: 'actions', name: 'actions', orderable: false, searchable: false }
            ]
        });
    });

    function formatRupiah(num) {
        return 'Rp ' + Math.round(num).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    function calculateSim() {
        const option = $('#sim_platform option:selected');
        const commPercent = parseFloat(option.data('commission')) || 0;
        const fixedFee = parseInt(option.data('fixed')) || 0;
        const gross = parseFloat(($('#sim_gross').val() || '').replace(/\D/g, '')) || 0;
        const other = parseFloat(($('#sim_other').val() || '').replace(/\D/g, '')) || 0;

        const platformFee = Math.round((gross * commPercent) / 100) + fixedFee;
        const net = Math.round(gross - platformFee - other);

        $('#res_fee').text(formatRupiah(platformFee));
        $('#res_other').text(formatRupiah(other));
        $('#res_net').text(formatRupiah(net));
    }

    $(document).ready(function() {
        bindRupiahInputs();
        $('#sim_platform, #sim_gross, #sim_other').on('change input', calculateSim);
        calculateSim();
    });
</script>
@endsection
