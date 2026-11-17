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
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Nama Platform</th>
                                <th>Kode</th>
                                <th>Komisi (%)</th>
                                <th>Biaya Tetap</th>
                                <th>Biaya Tarik</th>
                                <th>Status</th>
                                <th>Klien</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($platforms as $platform)
                                <tr>
                                    <td class="font-weight-bold text-dark">
                                        {{ $platform->name }}
                                        @if($platform->notes)
                                            <div class="small text-muted font-weight-normal">{{ $platform->notes }}</div>
                                        @endif
                                    </td>
                                    <td><span class="badge badge-secondary">{{ $platform->code }}</span></td>
                                    <td class="font-weight-bold text-primary">{{ number_format($platform->commission_percent, 2) }}%</td>
                                    <td>{{ format_rupiah($platform->fixed_fee) }}</td>
                                    <td>{{ format_rupiah($platform->withdrawal_fee) }}</td>
                                    <td>
                                        @if($platform->is_active)
                                            <span class="badge badge-success">Aktif</span>
                                        @else
                                            <span class="badge badge-danger">Nonaktif</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-light border">{{ $platform->clients_count }}</span>
                                    </td>
                                    <td class="text-right">
                                        <div class="btn-group">
                                            <a href="{{ route('platforms.edit', $platform) }}" class="btn btn-sm btn-outline-secondary" title="Ubah">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-danger" title="Hapus" data-toggle="modal" data-target="#deleteModal{{ $platform->id }}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>

                                        <!-- Modal Delete -->
                                        <div class="modal fade" id="deleteModal{{ $platform->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered text-left">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title font-weight-bold">Konfirmasi Hapus</h5>
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        Apakah Anda yakin ingin menghapus platform <strong>{{ $platform->name }}</strong>?
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                                        <form action="{{ route('platforms.destroy', $platform) }}" method="POST">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-danger font-weight-bold">Ya, Hapus</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">
                                        Belum ada platform.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
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
                    <input type="number" id="sim_gross" class="form-control" value="1000000" step="1000">
                </div>

                <div class="form-group">
                    <label for="sim_other">Biaya Lain / Extra (Rp)</label>
                    <input type="number" id="sim_other" class="form-control" value="0" step="1000">
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
    function formatRupiah(num) {
        return 'Rp ' + Math.round(num).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    function calculateSim() {
        const option = $('#sim_platform option:selected');
        const commPercent = parseFloat(option.data('commission')) || 0;
        const fixedFee = parseInt(option.data('fixed')) || 0;
        const gross = parseFloat($('#sim_gross').val()) || 0;
        const other = parseFloat($('#sim_other').val()) || 0;

        const platformFee = Math.round((gross * commPercent) / 100) + fixedFee;
        const net = Math.round(gross - platformFee - other);

        $('#res_fee').text(formatRupiah(platformFee));
        $('#res_other').text(formatRupiah(other));
        $('#res_net').text(formatRupiah(net));
    }

    $(document).ready(function() {
        $('#sim_platform, #sim_gross, #sim_other').on('change input', calculateSim);
        calculateSim();
    });
</script>
@endsection
