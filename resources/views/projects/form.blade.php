@extends('layouts.admin')

@section('title', $project->exists ? 'Ubah Proyek' : 'Tambah Proyek')
@section('page_title', $project->exists ? 'Ubah Proyek' : 'Tambah Proyek')

@section('content')
@php
    $selectedOrderId = old('order_id', $project->order_id);
    $selectedOrder = $orders->firstWhere('id', $selectedOrderId);
    $initialClientName = $selectedOrder?->client?->name ?? $project->client?->name ?? '';
@endphp

<form method="POST" action="{{ $project->exists ? route('projects.update', $project) : route('projects.store') }}" id="projectForm">
    @csrf
    @if($project->exists)
        @method('PUT')
    @endif

    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header">
            <h3 class="card-title font-weight-bold">
                <i class="fas fa-tasks mr-1 text-primary"></i> Informasi Proyek Berdasarkan Order
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
                                <option 
                                    value="{{ $order->id }}"
                                    data-title="{{ $order->title }}"
                                    data-client-id="{{ $order->client_id }}"
                                    data-client-name="{{ $order->client?->name ?? '-' }}"
                                    data-deadline="{{ $order->deadline?->format('Y-m-d') ?? '' }}"
                                    data-start-date="{{ $order->ordered_at?->format('Y-m-d') ?? '' }}"
                                    @selected($selectedOrderId == $order->id)
                                >
                                    [ORD-{{ $order->id }}] {{ $order->title }} &mdash; {{ $order->client?->name ?? 'Tanpa Klien' }}
                                </option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">Proyek dibuat dan terikat langsung dengan data order pekerjaan.</small>
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
                            value="{{ old('client_id', $project->client_id ?? $selectedOrder?->client_id) }}"
                        >
                        <small class="form-text text-muted">Ditentukan otomatis berdasarkan klien pada order pekerjaan.</small>
                    </div>
                </div>
            </div>

            <!-- Nama Proyek -->
            <div class="form-group">
                <label for="name" class="font-weight-bold">Nama Proyek <span class="text-danger">*</span></label>
                <input 
                    type="text" 
                    name="name" 
                    id="name" 
                    class="form-control @error('name') is-invalid @enderror" 
                    value="{{ old('name', $project->name) }}" 
                    placeholder="Contoh: Pengembangan Aplikasi Web & Mobile..." 
                    required
                >
                <small class="form-text text-muted">Bisa disesuaikan atau otomatis mengikuti judul order pekerjaan.</small>
                @error('name')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <div class="row">
                <!-- Tanggal Mulai -->
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="start_date" class="font-weight-bold">Tanggal Mulai</label>
                        <input 
                            type="date" 
                            name="start_date" 
                            id="start_date" 
                            class="form-control @error('start_date') is-invalid @enderror" 
                            value="{{ old('start_date', $project->start_date?->format('Y-m-d') ?? ($selectedOrder?->ordered_at?->format('Y-m-d') ?? now()->format('Y-m-d'))) }}"
                            placeholder="YYYY-MM-DD (Tanggal Mulai)"
                        >
                        <small class="form-text text-muted">Awal pengerjaan proyek dimulai.</small>
                        @error('start_date')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Target Selesai -->
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="target_date" class="font-weight-bold">Target Selesai</label>
                        <input 
                            type="date" 
                            name="target_date" 
                            id="target_date" 
                            class="form-control @error('target_date') is-invalid @enderror" 
                            value="{{ old('target_date', $project->target_date?->format('Y-m-d') ?? $selectedOrder?->deadline?->format('Y-m-d')) }}"
                            placeholder="YYYY-MM-DD (Target Selesai)"
                        >
                        <small class="form-text text-muted">Batas waktu (deadline) penyelesaian proyek.</small>
                        @error('target_date')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Status Proyek -->
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="status" class="font-weight-bold">Status Proyek <span class="text-danger">*</span></label>
                        <select name="status" id="status" class="form-control @error('status') is-invalid @enderror" required>
                            <option value="">-- Pilih Status Proyek --</option>
                            @foreach([
                                'planning' => 'Planning (Perencanaan)',
                                'running' => 'Running (Sedang Berjalan)',
                                'on_hold' => 'On Hold (Ditunda)',
                                'done' => 'Done (Selesai)'
                            ] as $stKey => $stLabel)
                                <option value="{{ $stKey }}" @selected(old('status', $project->status ?: 'planning') === $stKey)>
                                    {{ $stLabel }}
                                </option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">Status umum fase pengerjaan proyek.</small>
                        @error('status')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Deskripsi -->
            <div class="form-group">
                <label for="description" class="font-weight-bold">Deskripsi / Ruang Lingkup Proyek</label>
                <textarea 
                    name="description" 
                    id="description" 
                    rows="3" 
                    class="form-control @error('description') is-invalid @enderror" 
                    placeholder="Tuliskan gambaran umum, teknologi, atau deliverable utama proyek ini..."
                >{{ old('description', $project->description) }}</textarea>
                @error('description')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            <hr class="my-4">

            <!-- Tahapan Progress Proyek (Untuk Kanban) -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="font-weight-bold mb-0">
                        <i class="fas fa-stream mr-1 text-primary"></i> Tahapan &amp; Milestone Progress Proyek
                    </h5>
                    <small class="text-muted">Setiap tahapan akan tampil sebagai kartu pada Kanban Progress board. Total bobot harus 100%.</small>
                </div>
                <div class="d-flex align-items-center">
                    <span class="badge badge-light border mr-3 p-2 font-weight-bold" id="totalWeightBadge">
                        Total Bobot: <span id="totalWeightValue">0</span>%
                    </span>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="btnAddStage">
                        <i class="fas fa-plus mr-1"></i> Tambah Tahapan
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="stagesTable">
                    <thead class="thead-light">
                        <tr>
                            <th style="width: 35%;">Nama Tahapan / Tugas <span class="text-danger">*</span></th>
                            <th style="width: 15%;">Bobot (%) <span class="text-danger">*</span></th>
                            <th style="width: 15%;">Progress (%)</th>
                            <th style="width: 15%;">Status Kanban</th>
                            <th style="width: 15%;">Target Selesai</th>
                            <th style="width: 5%; text-align: center;">Hapus</th>
                        </tr>
                    </thead>
                    <tbody id="stagesTbody">
                        @foreach($defaultStages as $i => $stage)
                            @php
                                $sObj = is_object($stage) ? $stage : (object) $stage;
                                $sName = old("stages.$i.name", $sObj->name ?? '');
                                $sWeight = old("stages.$i.weight", $sObj->weight ?? 0);
                                $sProgress = old("stages.$i.progress_percent", $sObj->progress_percent ?? 0);
                                $sStatus = old("stages.$i.status", $sObj->status ?? 'todo');
                                $sTarget = old("stages.$i.target_date", isset($sObj->target_date) ? ($sObj->target_date instanceof \DateTimeInterface ? $sObj->target_date->format('Y-m-d') : $sObj->target_date) : '');
                            @endphp
                            <tr class="stage-row">
                                <td>
                                    <input 
                                        type="text" 
                                        name="stages[{{ $i }}][name]" 
                                        class="form-control stage-name-input" 
                                        value="{{ $sName }}" 
                                        placeholder="Nama tahapan (contoh: Desain UI/UX)..." 
                                        required
                                    >
                                </td>
                                <td>
                                    <div class="input-group">
                                        <input 
                                            type="number" 
                                            name="stages[{{ $i }}][weight]" 
                                            class="form-control stage-weight-input" 
                                            value="{{ $sWeight }}" 
                                            min="0" 
                                            max="100" 
                                            placeholder="Bobot %" 
                                            required
                                        >
                                        <div class="input-group-append"><span class="input-group-text">%</span></div>
                                    </div>
                                </td>
                                <td>
                                    <div class="input-group">
                                        <input 
                                            type="number" 
                                            name="stages[{{ $i }}][progress_percent]" 
                                            class="form-control stage-progress-input" 
                                            value="{{ $sProgress }}" 
                                            min="0" 
                                            max="100" 
                                            placeholder="0 - 100" 
                                            required
                                        >
                                        <div class="input-group-append"><span class="input-group-text">%</span></div>
                                    </div>
                                </td>
                                <td>
                                    <select name="stages[{{ $i }}][status]" class="form-control stage-status-select">
                                        <option value="todo" @selected($sStatus === 'todo')>Belum Dimulai</option>
                                        <option value="doing" @selected($sStatus === 'doing')>Sedang Dikerjakan</option>
                                        <option value="review" @selected($sStatus === 'review')>Revisi / Review</option>
                                        <option value="done" @selected($sStatus === 'done')>Selesai</option>
                                    </select>
                                </td>
                                <td>
                                    <input 
                                        type="date" 
                                        name="stages[{{ $i }}][target_date]" 
                                        class="form-control" 
                                        value="{{ $sTarget }}"
                                        placeholder="YYYY-MM-DD"
                                    >
                                </td>
                                <td class="text-center align-middle">
                                    <button type="button" class="btn btn-sm btn-outline-danger btn-remove-stage" title="Hapus tahapan">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
        <div class="card-footer bg-light d-flex justify-content-between">
            <a href="{{ route('projects.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Batal
            </a>
            <button type="submit" class="btn btn-primary font-weight-bold">
                <i class="fas fa-save mr-1"></i> {{ $project->exists ? 'Simpan Perubahan' : 'Buat Proyek' }}
            </button>
        </div>
    </div>
</form>
@endsection

@section('scripts')
<script>
    $(function () {
        // 1. Sinkronisasi Order -> Klien, Nama Proyek, & Deadline
        $('#order_id').on('change', function () {
            const selected = $(this).find('option:selected');
            const title = selected.data('title');
            const clientId = selected.data('client-id');
            const clientName = selected.data('client-name');
            const deadline = selected.data('deadline');
            const startDate = selected.data('start-date');

            if ($(this).val()) {
                $('#client_name_display').val(clientName || '');
                $('#client_id').val(clientId || '');

                // Isi nama proyek jika masih kosong
                if (!$('#name').val().trim() && title) {
                    $('#name').val(title);
                }

                // Isi deadline jika masih kosong
                if (!$('#target_date').val() && deadline) {
                    $('#target_date').val(deadline);
                }

                if (!$('#start_date').val() && startDate) {
                    $('#start_date').val(startDate);
                }
            } else {
                $('#client_name_display').val('');
                $('#client_id').val('');
            }
        });

        // 2. Hitung Total Bobot
        function calculateTotalWeight() {
            let total = 0;
            $('.stage-weight-input').each(function () {
                const val = parseInt($(this).val(), 10);
                if (!isNaN(val)) total += val;
            });

            $('#totalWeightValue').text(total);
            const badge = $('#totalWeightBadge');

            if (total === 100) {
                badge.removeClass('border-danger text-danger border-warning text-warning')
                     .addClass('border-success text-success bg-light');
            } else {
                badge.removeClass('border-success text-success')
                     .addClass('border-danger text-danger bg-light');
            }
        }

        $(document).on('input change', '.stage-weight-input', calculateTotalWeight);
        calculateTotalWeight();

        // 3. Tambah Baris Tahapan Baru
        $('#btnAddStage').on('click', function () {
            const index = $('#stagesTbody tr').length;
            const newRow = `
                <tr class="stage-row">
                    <td>
                        <input type="text" name="stages[${index}][name]" class="form-control stage-name-input" placeholder="Nama tahapan baru..." required>
                    </td>
                    <td>
                        <div class="input-group">
                            <input type="number" name="stages[${index}][weight]" class="form-control stage-weight-input" value="0" min="0" max="100" placeholder="Bobot %" required>
                            <div class="input-group-append"><span class="input-group-text">%</span></div>
                        </div>
                    </td>
                    <td>
                        <div class="input-group">
                            <input type="number" name="stages[${index}][progress_percent]" class="form-control stage-progress-input" value="0" min="0" max="100" placeholder="0 - 100" required>
                            <div class="input-group-append"><span class="input-group-text">%</span></div>
                        </div>
                    </td>
                    <td>
                        <select name="stages[${index}][status]" class="form-control stage-status-select">
                            <option value="todo">Belum Dimulai</option>
                            <option value="doing">Sedang Dikerjakan</option>
                            <option value="review">Revisi / Review</option>
                            <option value="done">Selesai</option>
                        </select>
                    </td>
                    <td>
                        <input type="date" name="stages[${index}][target_date]" class="form-control" placeholder="YYYY-MM-DD">
                    </td>
                    <td class="text-center align-middle">
                        <button type="button" class="btn btn-sm btn-outline-danger btn-remove-stage" title="Hapus tahapan">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </td>
                </tr>
            `;
            $('#stagesTbody').append(newRow);
            calculateTotalWeight();
        });

        // 4. Hapus Baris Tahapan
        $(document).on('click', '.btn-remove-stage', function () {
            if ($('#stagesTbody tr').length <= 1) {
                alert('Proyek minimal harus memiliki satu tahapan progress.');
                return;
            }
            $(this).closest('tr').remove();
            calculateTotalWeight();
        });
    });
</script>
@endsection
