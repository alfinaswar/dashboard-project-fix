@extends('layouts.admin')
@section('title', 'Tarif Potongan') @section('page_title', 'Tarif Potongan: '.$platform->name)
@section('page_actions')<a href="{{ route('platforms.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left mr-1"></i> Kembali</a>@endsection

@section('content')
<div class="row">
    <div class="col-lg-7">
        <div class="card card-outline card-primary">
            <div class="card-header bg-white"><h3 class="card-title font-weight-bold"><i class="fas fa-history text-primary mr-1"></i> Riwayat Tarif</h3></div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="thead-light"><tr><th>Berlaku</th><th>Komponen Potongan</th><th>Catatan</th><th>Aksi</th></tr></thead>
                    <tbody>
                    @forelse($rules as $rule)
                        <tr>
                            <td>
                                <strong>{{ $rule->effective_from->format('d/m/Y') }}</strong>
                                <div class="small text-muted">s.d. {{ $rule->effective_to?->format('d/m/Y') ?? 'seterusnya' }}</div>
                            </td>
                            <td>
                                @forelse($rule->components as $component)
                                    <div class="small">
                                        <span class="badge badge-light border">{{ \App\Models\PlatformFeeComponent::TYPES[$component->type] ?? $component->type }}</span>
                                        <strong>{{ $component->type === 'percent' ? number_format((float) $component->value, 2) : format_rupiah((int) $component->value) }}</strong>
                                        @if($component->note)<span class="text-muted">— {{ $component->note }}</span>@endif
                                    </div>
                                @empty
                                    <span class="text-muted small">Tidak ada komponen.</span>
                                @endforelse
                            </td>
                            <td class="small text-muted">{{ $rule->note ?: '-' }}</td>
                            <td>
                                <form method="POST" action="{{ route('platforms.fee-rules.destroy', [$platform, $rule]) }}">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus tarif ini? Order lama tidak terpengaruh.')"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">Belum ada tarif. Order akan memakai formula lama: komisi {{ number_format((float) $platform->commission_percent, 2) }}% + {{ format_rupiah($platform->fixed_fee) }}.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card card-outline card-success">
            <div class="card-header bg-white"><h3 class="card-title font-weight-bold"><i class="fas fa-plus text-success mr-1"></i> Tambah Tarif</h3></div>
            <form method="POST" action="{{ route('platforms.fee-rules.store', $platform) }}">
                @csrf
                <div class="card-body">
                    <div class="row">
                        <div class="col-6"><div class="form-group"><label>Mulai Berlaku *</label><input type="date" name="effective_from" class="form-control" value="{{ old('effective_from', now()->format('Y-m-d')) }}" required></div></div>
                        <div class="col-6"><div class="form-group"><label>Selesai (opsional)</label><input type="date" name="effective_to" class="form-control" value="{{ old('effective_to') }}"></div></div>
                    </div>
                    <div class="form-group"><label>Catatan</label><input name="note" class="form-control" value="{{ old('note') }}" placeholder="mis. tarif Fastwork periode Juli"></div>

                    <hr>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <strong>Komponen Potongan</strong>
                        <button type="button" id="add-component" class="btn btn-sm btn-outline-primary"><i class="fas fa-plus"></i> Tambah</button>
                    </div>
                    <div id="components"></div>
                    <p class="small text-muted mb-0">Rumus: <code>potongan = (Σ persen × kotor) + Σ nominal tetap</code>, lalu dibatasi minimal/maksimal bila diisi.</p>
                    @error('components')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="card-footer"><button class="btn btn-success">Simpan Tarif</button></div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const feeTypes = @json(\App\Models\PlatformFeeComponent::TYPES);
    let componentIndex = 0;

    function addComponent(type = 'percent', value = '') {
        const index = componentIndex++;
        const options = Object.entries(feeTypes).map(([key, label]) =>
            `<option value="${key}" ${key === type ? 'selected' : ''}>${label}</option>`
        ).join('');

        $('#components').append(`
            <div class="form-row align-items-center mb-1 component-row">
                <div class="col-6"><select name="components[${index}][type]" class="form-control form-control-sm component-type">${options}</select></div>
                <div class="col-4">
                    <div class="input-group input-group-sm">
                        <input name="components[${index}][value]" type="number" step="0.01" min="0" value="${value}" class="form-control" required>
                        <div class="input-group-append"><span class="input-group-text component-suffix">%</span></div>
                    </div>
                </div>
                <div class="col-2 text-right"><button type="button" class="btn btn-sm btn-outline-danger remove-component"><i class="fas fa-times"></i></button></div>
            </div>
        `);
    }

    function syncSuffix() {
        $('.component-row').each(function () {
            $(this).find('.component-suffix').text($(this).find('.component-type').val() === 'percent' ? '%' : 'Rp');
        });
    }

    $(function () {
        addComponent();
        $('#add-component').on('click', function () { addComponent(); });
        $(document).on('change', '.component-type', syncSuffix);
        $(document).on('click', '.remove-component', function () {
            if ($('.component-row').length > 1) {
                $(this).closest('.component-row').remove();
            }
        });
        syncSuffix();
    });
</script>
@endsection
