@extends('layouts.admin')

@section('title', 'Kanban Progress: ' . $project->name)
@section('page_title', 'Progress Proyek')

@section('page_actions')
<div class="btn-group mr-2">
    <a href="{{ route('meetings.index', ['project_id' => $project->id]) }}" class="btn btn-warning font-weight-bold text-dark">
        <i class="fas fa-sticky-note mr-1"></i> Meeting Notes ({{ $project->meetings->count() }})
    </a>
    <a href="{{ route('projects.edit', $project) }}" class="btn btn-outline-primary">
        <i class="fas fa-edit mr-1"></i> Ubah Proyek
    </a>
</div>
<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalAddStage">
    <i class="fas fa-plus mr-1"></i> Tambah Tahapan
</button>
<a href="{{ route('projects.index') }}" class="btn btn-secondary ml-1">
    <i class="fas fa-arrow-left mr-1"></i> Kembali
</a>
@endsection

@section('styles')
<style>
    /* Project Header Summary Card */
    .project-header-box {
        background: #FFFFFF;
        border-radius: 14px;
        padding: 1.5rem 1.75rem;
        border: 1px solid #E2E8F0;
        box-shadow: 0 2px 6px rgba(0,0,0,0.03);
        margin-bottom: 1.5rem;
    }
    .order-tag {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        background-color: #EFF6FF;
        color: #1D4ED8;
        border: 1px solid #BFDBFE;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 0.82rem;
        font-weight: 700;
        text-decoration: none !important;
    }

    /* Kanban Board Layout */
    .kanban-progress-board {
        display: flex;
        gap: 1.25rem;
        overflow-x: auto;
        padding-bottom: 1.5rem;
        min-height: 520px;
    }
    .kanban-stage-col {
        flex: 1;
        min-width: 280px;
        max-width: 340px;
        background: #F8FAFC;
        border: 1.5px solid #E2E8F0;
        border-radius: 12px;
        display: flex;
        flex-direction: column;
    }
    .stage-col-header {
        padding: 0.9rem 1.1rem;
        background: #FFFFFF;
        border-bottom: 1.5px solid #E2E8F0;
        border-top-left-radius: 12px;
        border-top-right-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .stage-col-todo { border-top: 4px solid #64748B; }
    .stage-col-doing { border-top: 4px solid #3B82F6; }
    .stage-col-review { border-top: 4px solid #F59E0B; }
    .stage-col-done { border-top: 4px solid #10B981; }

    .stage-col-body {
        padding: 0.85rem;
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 0.85rem;
        overflow-y: auto;
    }

    /* Stage Kanban Card */
    .stage-card {
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 10px;
        padding: 1.1rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .stage-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        border-color: #CBD5E1;
    }
    .stage-card-title {
        font-size: 0.96rem;
        font-weight: 700;
        color: #0F172A;
        line-height: 1.35;
        margin-bottom: 0.5rem;
    }
    .stage-weight-pill {
        display: inline-block;
        background: #F1F5F9;
        border: 1px solid #E2E8F0;
        color: #475569;
        font-size: 0.72rem;
        font-weight: 700;
        padding: 2px 7px;
        border-radius: 4px;
    }

    /* Meeting Sticky Notes Preview inside Project */
    .sticky-note-mini {
        border-radius: 8px;
        padding: 1rem;
        box-shadow: 0 4px 10px rgba(0,0,0,0.06);
        position: relative;
        transition: transform 0.2s ease;
        border: 1px solid rgba(0,0,0,0.06);
    }
    .sticky-note-mini:hover {
        transform: translateY(-2px) scale(1.01);
    }
    .pin-icon {
        position: absolute;
        top: 6px;
        right: 10px;
        font-size: 0.9rem;
    }
</style>
@endsection

@section('content')

<!-- ==================== HEADER INFO & OVERALL PROGRESS ==================== -->
<div class="project-header-box">
    <div class="row align-items-center">
        <div class="col-lg-8">
            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                @if($project->order)
                    <a href="{{ route('orders.show', $project->order) }}" class="order-tag">
                        <i class="fas fa-file-invoice"></i> ORD-{{ $project->order->id }}: {{ Str::limit($project->order->title, 35) }}
                    </a>
                @endif
                <span class="badge badge-light border text-muted px-2 py-1 ml-2">
                    <i class="far fa-user mr-1 text-primary"></i> Klien: {{ $project->order?->client?->name ?? $project->client?->name ?? 'Tanpa Klien' }}
                </span>
                <span class="badge badge-{{ $project->status === 'done' ? 'success' : ($project->status === 'running' ? 'primary' : ($project->status === 'on_hold' ? 'warning' : 'secondary')) }} px-2 py-1 ml-2 text-uppercase font-weight-bold" id="badgeProjectStatus">
                    {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                </span>
            </div>

            <h3 class="font-weight-bold mb-1 text-dark">{{ $project->name }}</h3>
            @if($project->description)
                <p class="text-muted mb-2 small">{{ $project->description }}</p>
            @endif

            <div class="d-flex flex-wrap text-muted small mt-2">
                <span class="mr-3">
                    <i class="far fa-calendar-check mr-1 text-primary"></i> Mulai: <strong>{{ $project->start_date ? $project->start_date->format('d/m/Y') : '-' }}</strong>
                </span>
                <span class="mr-3">
                    <i class="far fa-calendar-times mr-1 text-danger"></i> Target Selesai: <strong>{{ $project->target_date ? $project->target_date->format('d/m/Y') : '-' }}</strong>
                </span>
                <span>
                    <i class="fas fa-layer-group mr-1 text-info"></i> Total: <strong>{{ $project->stages->count() }} Tahapan</strong>
                </span>
            </div>
        </div>

        <!-- Master Overall Progress Meter -->
        <div class="col-lg-4 mt-3 mt-lg-0 text-lg-right">
            <div class="p-3 bg-light rounded border text-center">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="font-weight-bold text-muted small text-uppercase">Total Progress Proyek</span>
                    <span class="font-weight-bold text-primary h5 mb-0" id="overallProgressText">{{ $project->progress_percent }}%</span>
                </div>
                <div class="progress" style="height: 12px; border-radius: 99px;">
                    <div 
                        id="overallProgressBar" 
                        class="progress-bar progress-bar-striped progress-bar-animated {{ $project->progress_percent >= 100 ? 'bg-success' : 'bg-primary' }}" 
                        style="width: {{ $project->progress_percent }}%"
                    ></div>
                </div>
                <small class="text-muted d-block mt-2">Dihitung otomatis dari bobot &amp; progress setiap tahapan.</small>
            </div>
        </div>
    </div>
</div>

<!-- ==================== KANBAN PROGRESS BOARD ==================== -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="font-weight-bold mb-0 text-dark">
        <i class="fas fa-columns mr-1 text-primary"></i> Kanban Board Progress Tahapan
    </h5>
    <small class="text-muted">Pindahkan tahapan antar kolom untuk memperbarui progress secara real-time.</small>
</div>

<div class="kanban-progress-board">
    @php
        $columns = [
            'todo' => [
                'title' => 'Belum Dimulai',
                'sub' => 'To Do / Antrean',
                'icon' => 'fas fa-hourglass-start',
                'color' => 'secondary',
                'borderClass' => 'stage-col-todo',
            ],
            'doing' => [
                'title' => 'Sedang Dikerjakan',
                'sub' => 'In Progress / Koding',
                'icon' => 'fas fa-spinner fa-spin',
                'color' => 'primary',
                'borderClass' => 'stage-col-doing',
            ],
            'review' => [
                'title' => 'Revisi / Review',
                'sub' => 'UAT & Feedback Klien',
                'icon' => 'fas fa-clipboard-check',
                'color' => 'warning',
                'borderClass' => 'stage-col-review',
            ],
            'done' => [
                'title' => 'Selesai',
                'sub' => 'Completed / Done',
                'icon' => 'fas fa-check-circle',
                'color' => 'success',
                'borderClass' => 'stage-col-done',
            ],
        ];
    @endphp

    @foreach($columns as $colKey => $colMeta)
        @php
            $stagesInCol = $stagesByColumn[$colKey] ?? collect();
        @endphp
        <div class="kanban-stage-col" data-col="{{ $colKey }}">
            <div class="stage-col-header {{ $colMeta['borderClass'] }}">
                <div>
                    <h6 class="font-weight-bold mb-0 text-dark">
                        <i class="{{ $colMeta['icon'] }} mr-1 text-{{ $colMeta['color'] }}"></i> {{ $colMeta['title'] }}
                    </h6>
                    <small class="text-muted">{{ $colMeta['sub'] }}</small>
                </div>
                <span class="badge badge-{{ $colMeta['color'] }} badge-pill" id="count-{{ $colKey }}">{{ $stagesInCol->count() }}</span>
            </div>

            <div class="stage-col-body" id="col-list-{{ $colKey }}">
                @forelse($stagesInCol as $stage)
                    <div class="stage-card" id="stage-card-{{ $stage->id }}" data-stage-id="{{ $stage->id }}">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="stage-weight-pill">Bobot: {{ $stage->weight }}%</span>
                            <span class="badge badge-light border text-muted small">
                                <i class="far fa-calendar-alt mr-1"></i> {{ $stage->target_date ? $stage->target_date->format('d/m/Y') : 'Tanpa Target' }}
                            </span>
                        </div>

                        <div class="stage-card-title">{{ $stage->name }}</div>

                        <!-- Mini Progress Slider / Display -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between small text-muted mb-1">
                                <span>Penyelesaian:</span>
                                <strong class="text-primary stage-progress-label">{{ $stage->progress_percent }}%</strong>
                            </div>
                            <div class="progress" style="height: 6px; border-radius: 99px;">
                                <div class="progress-bar bg-primary stage-progress-bar" style="width: {{ $stage->progress_percent }}%"></div>
                            </div>
                        </div>

                        <!-- Actions & Move Column -->
                        <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                            <div class="dropdown">
                                <button class="btn btn-xs btn-outline-primary dropdown-toggle" type="button" data-toggle="dropdown">
                                    Pindah Kolom
                                </button>
                                <div class="dropdown-menu">
                                    <h6 class="dropdown-header">Ubah Status Kanban</h6>
                                    @foreach($columns as $k => $m)
                                        @if($k !== $colKey)
                                            <a class="dropdown-item btn-move-stage" href="javascript:void(0);" data-stage-id="{{ $stage->id }}" data-target-status="{{ $k }}">
                                                <i class="{{ $m['icon'] }} mr-2 text-{{ $m['color'] }}"></i> Pindah ke: {{ $m['title'] }}
                                            </a>
                                        @endif
                                    @endforeach
                                </div>
                            </div>

                            <div class="btn-group">
                                <button 
                                    type="button" 
                                    class="btn btn-xs btn-outline-secondary btn-quick-progress" 
                                    data-stage-id="{{ $stage->id }}" 
                                    data-current-progress="{{ $stage->progress_percent }}" 
                                    data-stage-name="{{ $stage->name }}"
                                    title="Ubah % Progress"
                                >
                                    <i class="fas fa-sliders-h"></i>
                                </button>
                                <button 
                                    type="button" 
                                    class="btn btn-xs btn-outline-danger btn-delete-stage" 
                                    data-stage-id="{{ $stage->id }}" 
                                    data-stage-name="{{ $stage->name }}"
                                    title="Hapus Tahapan"
                                >
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted py-4 font-italic empty-notice" style="font-size: 0.82rem;">
                        Belum ada tahapan di kolom ini.
                    </div>
                @endforelse
            </div>
        </div>
    @endforeach
</div>

<!-- ==================== CATATAN MEETING (STICKY NOTES) KHUSUS PROYEK INI ==================== -->
<div class="card card-outline card-warning shadow-sm mt-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="card-title font-weight-bold mb-0 text-dark">
            <i class="fas fa-sticky-note mr-1 text-warning"></i> Catatan Meeting &amp; Notulen (Sticky Notes) Proyek Ini
        </h5>
        <div>
            <a href="{{ route('meetings.index', ['project_id' => $project->id]) }}" class="btn btn-sm btn-outline-warning text-dark font-weight-bold mr-1">
                <i class="fas fa-external-link-alt mr-1"></i> Buka Papan Lengkap
            </a>
            <button type="button" class="btn btn-sm btn-warning text-dark font-weight-bold" data-toggle="modal" data-target="#modalAddMeeting">
                <i class="fas fa-plus mr-1"></i> Tulis Sticky Note Meeting
            </button>
        </div>
    </div>
    <div class="card-body">
        @if($project->meetings->isEmpty())
            <div class="text-center py-4 text-muted">
                <i class="far fa-sticky-note fa-3x mb-2 text-warning" style="opacity: 0.5;"></i>
                <p class="mb-0">Belum ada catatan meeting untuk proyek ini. Klik tombol di atas untuk membuat sticky note pertama!</p>
            </div>
        @else
            <div class="row">
                @foreach($project->meetings as $meeting)
                    @php
                        $colorMeta = $meeting->color_meta;
                        $statusMeta = $meeting->status_meta;
                    @endphp
                    <div class="col-md-4 col-sm-6 mb-3">
                        <div class="sticky-note-mini" style="background-color: {{ $colorMeta['bg'] }}; color: {{ $colorMeta['text'] }}; border-color: {{ $colorMeta['border'] }};">
                            <span class="pin-icon" style="color: {{ $colorMeta['pin'] }};">📌</span>
                            <div class="d-flex justify-content-between align-items-center mb-1 pr-3">
                                <span class="badge badge-light border text-dark small" style="font-size: 0.72rem;">
                                    <i class="{{ $statusMeta['icon'] }} mr-1"></i> {{ $statusMeta['label'] }}
                                </span>
                                @if($meeting->meeting_date)
                                    <span class="small font-weight-bold" style="font-size: 0.72rem;">
                                        {{ $meeting->meeting_date->format('d M Y, H:i') }}
                                    </span>
                                @endif
                            </div>

                            <h6 class="font-weight-bold mt-2 mb-1" style="line-height: 1.3;">{{ $meeting->title }}</h6>

                            @if($meeting->attendees)
                                <div class="small mb-2" style="opacity: 0.85;">
                                    <i class="fas fa-users mr-1"></i> {{ $meeting->attendees }}
                                </div>
                            @endif

                            @if($meeting->notes)
                                <p class="small mb-2" style="white-space: pre-line; opacity: 0.95;">{{ Str::limit($meeting->notes, 120) }}</p>
                            @endif

                            @if($meeting->action_items)
                                <div class="p-1 px-2 rounded mb-1" style="background: rgba(255,255,255,0.45); font-size: 0.78rem;">
                                    <strong><i class="fas fa-check-square mr-1"></i> Action:</strong> {{ Str::limit($meeting->action_items, 80) }}
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

<!-- ==================== MODAL TAMBAH TAHAPAN ==================== -->
<div class="modal fade" id="modalAddStage" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form id="formAddStage">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title font-weight-bold">
                        <i class="fas fa-plus-circle mr-1 text-primary"></i> Tambah Tahapan Baru
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="new_stage_name" class="font-weight-bold">Nama Tahapan / Tugas <span class="text-danger">*</span></label>
                        <input type="text" id="new_stage_name" class="form-control" placeholder="Contoh: Implementasi Payment Gateway..." required>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label for="new_stage_weight" class="font-weight-bold">Bobot (%) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" id="new_stage_weight" class="form-control" value="10" min="1" max="100" placeholder="1 - 100" required>
                                    <div class="input-group-append"><span class="input-group-text">%</span></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label for="new_stage_status" class="font-weight-bold">Kolom Kanban <span class="text-danger">*</span></label>
                                <select id="new_stage_status" class="form-control" required>
                                    <option value="todo" selected>Belum Dimulai</option>
                                    <option value="doing">Sedang Dikerjakan</option>
                                    <option value="review">Revisi / Review</option>
                                    <option value="done">Selesai</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="new_stage_target" class="font-weight-bold">Target Tanggal Selesai</label>
                        <input type="date" id="new_stage_target" class="form-control" placeholder="YYYY-MM-DD">
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary font-weight-bold">Simpan ke Kanban</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ==================== MODAL QUICK PROGRESS PERCENT ==================== -->
<div class="modal fade" id="modalQuickProgress" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
        <div class="modal-content">
            <form id="formQuickProgress">
                <input type="hidden" id="quick_stage_id">
                <div class="modal-header">
                    <h6 class="modal-title font-weight-bold" id="quick_stage_title">Update Progress</h6>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body text-center">
                    <div class="h2 font-weight-bold text-primary mb-2" id="quick_progress_display">50%</div>
                    <input type="range" class="custom-range" id="quick_progress_slider" min="0" max="100" step="5">
                </div>
                <div class="modal-footer bg-light p-2">
                    <button type="submit" class="btn btn-sm btn-primary btn-block font-weight-bold">Perbarui Progress</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ==================== MODAL TAMBAH MEETING DI PROYEK ==================== -->
<div class="modal fade" id="modalAddMeeting" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form method="POST" action="{{ route('meetings.store') }}">
                @csrf
                <input type="hidden" name="project_id" value="{{ $project->id }}">
                <input type="hidden" name="order_id" value="{{ $project->order_id }}">
                
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title font-weight-bold">
                        <i class="fas fa-sticky-note mr-1"></i> Buat Catatan Meeting (Sticky Note)
                    </h5>
                    <button type="button" class="close text-dark" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="font-weight-bold">Judul Meeting <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" placeholder="Contoh: Diskusi Alur Database & API..." required>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Tanggal &amp; Waktu</label>
                                <input type="datetime-local" name="meeting_date" class="form-control" value="{{ now()->format('Y-m-d\TH:i') }}">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Kolom Status <span class="text-danger">*</span></label>
                                <select name="status" class="form-control" required>
                                    <option value="scheduled" selected>📌 Terjadwal</option>
                                    <option value="in_progress">💬 Sedang Dibahas</option>
                                    <option value="action_needed">⚡ Tindak Lanjut</option>
                                    <option value="done">✅ Selesai</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">Peserta Meeting</label>
                        <input type="text" name="attendees" class="form-control" placeholder="Contoh: Budi, Tim Klien, Tech Lead">
                    </div>

                    <!-- Warna Sticky Note -->
                    <div class="form-group">
                        <label class="font-weight-bold">Warna Kertas Sticky Note <span class="text-danger">*</span></label>
                        <div class="d-flex gap-2">
                            @foreach(\App\Models\Meeting::COLORS as $cKey => $cMeta)
                                <label class="mr-2" style="cursor: pointer;">
                                    <input type="radio" name="color" value="{{ $cKey }}" class="sr-only" @checked($cKey === 'yellow')>
                                    <span class="d-inline-block rounded-circle border shadow-sm p-3" style="background-color: {{ $cMeta['bg'] }}; border-color: {{ $cMeta['border'] }} !important;" title="{{ $cMeta['name'] }}"></span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">Poin Pembahasan / Notulen</label>
                        <textarea name="notes" rows="3" class="form-control" placeholder="Tuliskan hasil diskusi atau ringkasan meeting..."></textarea>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">Tindak Lanjut / Action Items</label>
                        <textarea name="action_items" rows="2" class="form-control" placeholder="Tugas yang harus diselesaikan selanjutnya..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning font-weight-bold text-dark">Tempel Sticky Note</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    $(function () {
        const projectId = {{ $project->id }};

        // 1. Move Stage to Another Kanban Column via Ajax
        $(document).on('click', '.btn-move-stage', function () {
            const stageId = $(this).data('stage-id');
            const targetStatus = $(this).data('target-status');

            $.ajax({
                url: `/projects/${projectId}/stages/${stageId}/status`,
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    status: targetStatus
                },
                success: function (res) {
                    if (res.success) {
                        location.reload(); // Reload to refresh column distribution and progress
                    }
                },
                error: function (xhr) {
                    alert('Gagal memindahkan tahapan: ' + (xhr.responseJSON?.message || 'Terjadi kesalahan'));
                }
            });
        });

        // 2. Quick Progress Percent Modal
        $(document).on('click', '.btn-quick-progress', function () {
            const id = $(this).data('stage-id');
            const name = $(this).data('stage-name');
            const progress = $(this).data('current-progress');

            $('#quick_stage_id').val(id);
            $('#quick_stage_title').text('Update: ' + name);
            $('#quick_progress_slider').val(progress);
            $('#quick_progress_display').text(progress + '%');
            $('#modalQuickProgress').modal('show');
        });

        $('#quick_progress_slider').on('input', function () {
            $('#quick_progress_display').text($(this).val() + '%');
        });

        $('#formQuickProgress').on('submit', function (e) {
            e.preventDefault();
            const stageId = $('#quick_stage_id').val();
            const progress = $('#quick_progress_slider').val();

            // Tentukan status otomatis jika 100% atau 0%
            let status = 'doing';
            if (parseInt(progress, 10) === 100) status = 'done';
            else if (parseInt(progress, 10) === 0) status = 'todo';

            $.ajax({
                url: `/projects/${projectId}/stages/${stageId}/status`,
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    status: status,
                    progress_percent: progress
                },
                success: function (res) {
                    if (res.success) {
                        $('#modalQuickProgress').modal('hide');
                        location.reload();
                    }
                },
                error: function (xhr) {
                    alert('Gagal memperbarui progress: ' + (xhr.responseJSON?.message || 'Terjadi kesalahan'));
                }
            });
        });

        // 3. Tambah Tahapan Baru via Ajax
        $('#formAddStage').on('submit', function (e) {
            e.preventDefault();
            const name = $('#new_stage_name').val();
            const weight = $('#new_stage_weight').val();
            const status = $('#new_stage_status').val();
            const targetDate = $('#new_stage_target').val();

            $.ajax({
                url: `/projects/${projectId}/stages`,
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    name: name,
                    weight: weight,
                    status: status,
                    target_date: targetDate
                },
                success: function (res) {
                    if (res.success) {
                        $('#modalAddStage').modal('hide');
                        location.reload();
                    }
                },
                error: function (xhr) {
                    alert('Gagal menambahkan tahapan: ' + (xhr.responseJSON?.message || 'Terjadi kesalahan'));
                }
            });
        });

        // 4. Hapus Tahapan via Ajax
        $(document).on('click', '.btn-delete-stage', function () {
            const stageId = $(this).data('stage-id');
            const name = $(this).data('stage-name');

            if (!confirm(`Apakah Anda yakin ingin menghapus tahapan '${name}'?`)) {
                return;
            }

            $.ajax({
                url: `/projects/${projectId}/stages/${stageId}`,
                method: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function (res) {
                    if (res.success) {
                        location.reload();
                    }
                },
                error: function (xhr) {
                    alert('Gagal menghapus tahapan: ' + (xhr.responseJSON?.message || 'Terjadi kesalahan'));
                }
            });
        });
    });
</script>
@endsection
