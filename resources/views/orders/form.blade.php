@extends('layouts.admin')
@section('title',$order->exists?'Ubah Order':'Tambah Order') @section('page_title',$order->exists?'Ubah Order':'Tambah Order')

@section('content')
<form method="POST" action="{{ $order->exists?route('orders.update',$order):route('orders.store') }}">
    <div class="card card-primary">
        <div class="card-body">
            @csrf @if($order->exists)@method('PUT')@endif

            {{-- Bagian 1: Informasi order --}}
            <h5 class="font-weight-bold text-primary border-bottom pb-2 mb-3"><i class="fas fa-briefcase mr-1"></i> Informasi Order</h5>
            <div class="row">
                <div class="col-md-6"><div class="form-group"><label>Klien *</label><select name="client_id" class="form-control select2" required><option value="">Pilih klien</option>@foreach($clients as $client)<option value="{{ $client->id }}" @selected(old('client_id',$order->client_id)==$client->id)>{{ $client->name }}</option>@endforeach</select></div></div>
                <div class="col-md-6"><div class="form-group"><label>Platform *</label><select name="platform_id" class="form-control" required><option value="">Pilih platform</option>@foreach($platforms as $platform)<option value="{{ $platform->id }}" @selected(old('platform_id',$order->platform_id)==$platform->id)>{{ $platform->name }}</option>@endforeach</select></div></div>
                <div class="col-md-8"><div class="form-group"><label>Judul Pekerjaan *</label><input placeholder="Masukkan judul" name="title" class="form-control" value="{{ old('title',$order->title) }}" required></div></div>
                <div class="col-md-4"><div class="form-group"><label>Tanggal Order *</label><input placeholder="Pilih tanggal" type="date" name="ordered_at" class="form-control" value="{{ old('ordered_at',$order->ordered_at?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required></div></div>
                <div class="col-md-4"><div class="form-group"><label>Deadline</label><input placeholder="Pilih tanggal" type="date" name="deadline" class="form-control" value="{{ old('deadline',$order->deadline?->format('Y-m-d')) }}"></div></div>
                <div class="col-md-4"><div class="form-group"><label>Status *</label><select name="status" class="form-control">@foreach(['draft','in_progress','delivered','completed','cancelled'] as $status)<option value="{{ $status }}" @selected(old('status',$order->status?:'draft')===$status)>{{ str_replace('_',' ',ucfirst($status)) }}</option>@endforeach</select></div></div>
                <div class="col-md-4"><div class="form-group"><label>Jumlah Revisi</label><input placeholder="Masukkan jumlah revisi" type="number" min="0" name="revision_count" class="form-control" value="{{ old('revision_count',$order->revision_count??0) }}"></div></div>
                <div class="col-md-8"><div class="form-group"><label>Referensi Order Eksternal</label><input placeholder="Masukkan referensi eksternal" name="external_order_ref" class="form-control" value="{{ old('external_order_ref',$order->external_order_ref) }}"></div></div>
                <div class="col-12"><div class="form-group"><label>Catatan</label><textarea placeholder="Masukkan catatan" name="notes" class="form-control">{{ old('notes',$order->notes) }}</textarea></div></div>
            </div>

            <hr class="my-4">

            {{-- Bagian 2: Harga --}}
            <h5 class="font-weight-bold text-success border-bottom pb-2 mb-3"><i class="fas fa-money-bill-wave mr-1"></i> Harga</h5>
            <div class="row">
                <div class="col-md-4"><div class="form-group"><label>Nilai Kotor *</label>
                    <div class="input-group"><div class="input-group-prepend"><span class="input-group-text">Rp</span></div>
                    <input id="gross_amount" type="text" inputmode="numeric" autocomplete="off" name="gross_amount" class="form-control rupiah-input" value="{{ old('gross_amount',number_format((float)($order->gross_amount??0),0,',','.')) }}" required></div>
                </div></div>
                <div class="col-md-4"><div class="form-group"><label>Biaya Lain</label>
                    <div class="input-group"><div class="input-group-prepend"><span class="input-group-text">Rp</span></div>
                    <input id="other_fee" type="text" inputmode="numeric" autocomplete="off" name="other_fee" class="form-control rupiah-input" value="{{ old('other_fee',number_format((float)($order->other_fee??0),0,',','.')) }}"></div>
                </div></div>
                <div class="col-md-4"><div class="form-group"><label>Catatan Biaya Lain</label><input placeholder="Masukkan catatan" name="other_fee_note" class="form-control" value="{{ old('other_fee_note',$order->other_fee_note) }}"></div></div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-2">
                <strong>Rincian Potongan</strong>
                <button type="button" id="add-fee-item" class="btn btn-sm btn-outline-primary"><i class="fas fa-plus mr-1"></i> Tambah Potongan</button>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-bordered mb-2">
                    <thead class="thead-light"><tr><th style="width:45%">Nama Potongan</th><th style="width:15%">Tipe</th><th style="width:25%">Nilai</th><th style="width:15%">Subtotal</th><th style="width:5%"></th></tr></thead>
                    <tbody id="fee-items"></tbody>
                    <tfoot class="thead-light"><tr><th colspan="3" class="text-right">Total Potongan</th><th><span id="fee-total">Rp 0</span></th><th></th></tr></tfoot>
                </table>
            </div>
            <p class="small text-muted">Pilih <strong>Rp</strong> untuk nominal tetap, atau <strong>%</strong> untuk persen dari nilai kotor. Kosongkan rincian untuk memakai potongan otomatis dari tarif platform.</p>

            <div class="alert alert-info mt-3 mb-0 d-flex justify-content-between align-items-center">
                <span class="font-weight-bold">Nilai Bersih</span>
                <span class="h4 mb-0 font-weight-bold" id="net-amount">Rp 0</span>
            </div>
        </div>
        <div class="card-footer"><a href="{{ route('orders.index') }}" class="btn btn-secondary">Batal</a> <button class="btn btn-primary">Simpan</button></div>
    </div>
</form>
@endsection

@section('scripts')
@php
    $feeItemSeed = old('fee_items', $order->feeItems->map(fn ($item) => [
        'label' => $item->label,
        'amount_type' => $item->amount_type,
        'amount' => $item->amount,
    ])->values());
@endphp
<script>
    const existingFeeItems = @json($feeItemSeed);

    let feeIndex = 0;

    function rupiah(value) {
        return 'Rp ' + Math.round(value || 0).toLocaleString('id-ID');
    }

    function addFeeItem(item = {}) {
        const index = feeIndex++;
        const type = item.amount_type === 'percent' ? 'percent' : 'amount';
        const raw = item.amount ?? '';

        $('#fee-items').append(`
            <tr class="fee-row">
                <td><input name="fee_items[${index}][label]" class="form-control form-control-sm" value="${item.label ?? ''}" placeholder="mis. Komisi Fastwork" required></td>
                <td>
                    <select name="fee_items[${index}][amount_type]" class="form-control form-control-sm fee-type">
                        <option value="amount" ${type === 'amount' ? 'selected' : ''}>Rp</option>
                        <option value="percent" ${type === 'percent' ? 'selected' : ''}>%</option>
                    </select>
                </td>
                <td>
                    <div class="input-group input-group-sm">
                        <input name="fee_items[${index}][amount]" type="text" inputmode="numeric" autocomplete="off" class="form-control fee-amount" value="${raw === '' ? '' : Number(raw).toLocaleString('id-ID')}" required>
                        <div class="input-group-append"><span class="input-group-text fee-suffix">${type === 'percent' ? '%' : 'Rp'}</span></div>
                    </div>
                </td>
                <td class="align-middle"><span class="fee-subtotal font-weight-bold">Rp 0</span></td>
                <td class="text-center align-middle"><button type="button" class="btn btn-sm btn-outline-danger remove-fee"><i class="fas fa-times"></i></button></td>
            </tr>
        `);
        recalculate();
    }

    function recalculate() {
        const gross = parseFloat(($('#gross_amount').val() || '').replace(/\D/g, '')) || 0;
        const other = parseFloat(($('#other_fee').val() || '').replace(/\D/g, '')) || 0;
        let total = 0;

        $('.fee-row').each(function () {
            const row = $(this);
            const isPercent = row.find('.fee-type').val() === 'percent';
            const input = row.find('.fee-amount');
            const value = parseFloat((input.val() || '').replace(/\D/g, '')) || 0;
            const subtotal = isPercent ? gross * value / 100 : value;

            row.find('.fee-suffix').text(isPercent ? '%' : 'Rp');
            row.find('.fee-subtotal').text(rupiah(subtotal));
            total += subtotal;
        });

        $('#fee-total').text(rupiah(total));
        $('#net-amount').text(rupiah(gross - total - other));
    }

    $(function () {
        if (existingFeeItems.length) {
            existingFeeItems.forEach(function (item) { addFeeItem(item); });
        } else {
            addFeeItem();
        }

        bindRupiahInputs();

        $('#add-fee-item').on('click', function () { addFeeItem(); });

        $(document).on('click', '.remove-fee', function () {
            $(this).closest('.fee-row').remove();
            recalculate();
        });

        // Persen boleh desimal, nominal hanya angka bulat.
        $(document).on('input', '.fee-amount', function () {
            const input = $(this);
            if (input.closest('.fee-row').find('.fee-type').val() === 'percent') {
                input.val(input.val().replace(/[^\d,]/g, ''));
                return;
            }
            maskRupiah(this);
            recalculate();
        });

        $(document).on('change', '.fee-type', recalculate);
        $(document).on('input', '#gross_amount, #other_fee', function () {
            maskRupiah(this);
            recalculate();
        });

        recalculate();
    });
</script>
@endsection
