@extends('layouts.admin')

@section('title', 'Catatan Meeting (Sticky Notes Kanban)')
@section('page_title', 'Catatan Meeting & Notulen')

@section('page_actions')
<button type="button" class="btn btn-warning font-weight-bold text-dark shadow-sm mr-2" data-toggle="modal" data-target="#modalCreateMeeting">
    <i class="fas fa-plus mr-1"></i> Buat Sticky Note Baru
</button>
<a href="{{ route('projects.index') }}" class="btn btn-outline-primary">
    <i class="fas fa-tasks mr-1"></i> Proyek &amp; Progress
</a>
@endsection

@section('styles')
<style>
    /* Sticky Notes Board Styling */
    .sticky-kanban-board {
        display: flex;
        gap: 1.25rem;
        overflow-x: auto;
        padding-bottom: 2rem;
        min-height: calc(100vh - 240px);
    }
    .sticky-kanban-col {
        flex: 1;
        min-width: 300px;
        max-width: 360px;
        background: #F8FAFC;
        border: 2px dashed #CBD5E1;
        border-radius: 16px;
        display: flex;
        flex-direction: column;
        padding: 0.85rem;
    }
    .sticky-col-header {
        background: #FFFFFF;
        border-radius: 12px;
        padding: 0.85rem 1rem;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        border: 1px solid #E2E8F0;
    }
    .col-header-scheduled { border-left: 5px solid #3B82F6; }
    .col-header-in_progress { border-left: 5px solid #06B6D4; }
    .col-header-action_needed { border-left: 5px solid #F59E0B; }
    .col-header-done { border-left: 5px solid #10B981; }

    .sticky-cards-container {
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
        flex: 1;
        overflow-y: auto;
        padding: 4px;
    }

    /* Authentic Post-It Sticky Note Card */
    .sticky-note-card {
        position: relative;
        border-radius: 4px 4px 16px 4px;
        padding: 1.25rem 1.15rem 1rem 1.15rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.08), 
                    0 10px 15px -3px rgba(0, 0, 0, 0.07),
                    0 1px 2px 0 rgba(0, 0, 0, 0.06);
        transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.2s ease;
        border: 1px solid rgba(0,0,0,0.06);
    }
    .sticky-note-card:hover {
        transform: translateY(-4px) scale(1.02);
        box-shadow: 0 14px 24px -4px rgba(0, 0, 0, 0.12), 0 8px 10px -5px rgba(0, 0, 0, 0.08);
        z-index: 10;
    }

    /* Red Pushpin / Tape Effect on Top */
    .pushpin-accent {
        position: absolute;
        top: -8px;
        left: 50%;
        transform: translateX(-50%);
        font-size: 1.25rem;
        filter: drop-shadow(0 2px 2px rgba(0,0,0,0.2));
        user-select: none;
    }

    .sticky-note-title {
        font-size: 1.05rem;
        font-weight: 800;
        line-height: 1.35;
        margin-top: 0.35rem;
        margin-bottom: 0.5rem;
    }

    .sticky-tag-pill {
        display: inline-block;
        font-size: 0.72rem;
        font-weight: 700;
        padding: 2px 8px;
        border-radius: 99px;
        background: rgba(255, 255, 255, 0.65);
        border: 1px solid rgba(0, 0, 0, 0.08);
        margin-bottom: 0.4rem;
    }

    .sticky-note-body {
        font-size: 0.88rem;
        line-height: 1.5;
        white-space: pre-line;
        margin-bottom: 0.75rem;
    }

    .sticky-action-box {
        background: rgba(255, 255, 255, 0.7);
        border-radius: 8px;
        padding: 0.6rem 0.75rem;
        font-size: 0.82rem;
        line-height: 1.4;
        border-left: 3px solid rgba(0,0,0,0.25);
        margin-bottom: 0.75rem;
    }

    .sticky-card-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-top: 0.6rem;
        border-top: 1px dashed rgba(0, 0, 0, 0.15);
        font-size: 0.76rem;
    }

    /* Color Palette Swatches */
    .color-swatch-radio {
        display: inline-block;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        cursor: pointer;
        border: 2px solid #FFFFFF;
        box-shadow: 0 0 0 1.5px #CBD5E1;
        transition: transform 0.15s ease;
    }
    .color-swatch-radio:hover {
        transform: scale(1.15);
    }
    input[type="radio"]:checked + .color-swatch-radio {
        box-shadow: 0 0 0 2.5px #2563EB;
        transform: scale(1.1);
    }
</style>
@endsection

@section('content')

<!-- ==================== FILTER & SEARCH BAR ==================== -->
<div class="card card-outline card-primary shadow-sm mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('meetings.index') }}" class="row align-items-center">
            <div class="col-md-4 col-sm-6 mb-2 mb-md-0">
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                    </div>
                    <input 
                        type="text" 
                        name="search" 
                        class="form-control" 
                        placeholder="Cari judul, agenda, atau peserta..." 
                        value="{{ $search }}"
                    >
                </div>
            </div>

            <div class="col-md-3 col-sm-6 mb-2 mb-md-0">
                <select name="order_id" class="form-control form-control-sm select2" data-placeholder="Filter Order...">
                    <option value="">-- Semua Order Pekerjaan --</option>
                    @foreach($orders as $ord)
                        <option value="{{ $ord->id }}" @selected($selectedOrderId == $ord->id)>
                            ORD-{{ $ord->id }}: {{ Str::limit($ord->title, 25) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3 col-sm-6 mb-2 mb-md-0">
                <select name="project_id" class="form-control form-control-sm select2" data-placeholder="Filter Proyek...">
                    <option value="">-- Semua Proyek --</option>
                    @foreach($projects as $prj)
                        <option value="{{ $prj->id }}" @selected($selectedProjectId == $prj->id)>
                            {{ Str::limit($prj->name, 28) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2 col-sm-6 text-right">
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="fas fa-filter mr-1"></i> Filter
                </button>
                @if($search || $selectedOrderId || $selectedProjectId)
                    <a href="{{ route('meetings.index') }}" class="btn btn-sm btn-outline-secondary ml-1" title="Reset Filter">
                        <i class="fas fa-undo"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- ==================== STICKY NOTES KANBAN BOARD ==================== -->
<div class="sticky-kanban-board">
    @foreach($kanbanColumns as $colKey => $colData)
        @php
            $colMeta = $colData['meta'];
            $items = $colData['items'];
            $headerClass = 'col-header-' . $colKey;
        @endphp
        <div class="sticky-kanban-col" data-col="{{ $colKey }}">
            <div class="sticky-col-header {{ $headerClass }}">
                <div>
                    <h6 class="font-weight-bold mb-0 text-dark">
                        <i class="{{ $colMeta['icon'] }} mr-1 text-{{ $colMeta['badge'] }}"></i> {{ $colMeta['label'] }}
                    </h6>
                </div>
                <span class="badge badge-{{ $colMeta['badge'] }} badge-pill px-2 py-1 font-weight-bold">
                    {{ $items->count() }}
                </span>
            </div>

            <div class="sticky-cards-container" id="sticky-container-{{ $colKey }}">
                @forelse($items as $meeting)
                    @php
                        $colorMeta = $meeting->color_meta;
                        // Random slight rotation for physical sticky note feel (-1.5deg to 1.5deg)
                        $rotations = [-1.5, -0.8, 0.5, 1.2, -1.0, 1.5];
                        $rot = $rotations[$meeting->id % count($rotations)];
                    @endphp
                    <div 
                        class="sticky-note-card" 
                        id="note-card-{{ $meeting->id }}" 
                        style="background-color: {{ $colorMeta['bg'] }}; color: {{ $colorMeta['text'] }}; border-color: {{ $colorMeta['border'] }}; transform: rotate({{ $rot }}deg);"
                        data-meeting-id="{{ $meeting->id }}"
                    >
                        <!-- Pushpin icon -->
                        <span class="pushpin-accent" style="color: {{ $colorMeta['pin'] }};">📌</span>

                        <!-- Order & Project Tags -->
                        <div class="d-flex flex-wrap gap-1 mb-1">
                            @if($meeting->order)
                                <span class="sticky-tag-pill" title="Order Terkait">
                                    <i class="fas fa-file-invoice mr-1"></i> ORD-{{ $meeting->order->id }}
                                </span>
                            @endif
                            @if($meeting->project)
                                <span class="sticky-tag-pill" title="Proyek Terkait">
                                    <i class="fas fa-tasks mr-1"></i> {{ Str::limit($meeting->project->name, 18) }}
                                </span>
                            @endif
                        </div>

                        <!-- Title -->
                        <div class="sticky-note-title">{{ $meeting->title }}</div>

                        <!-- Date & Attendees -->
                        <div class="small mb-2 font-weight-bold" style="opacity: 0.9;">
                            @if($meeting->meeting_date)
                                <div><i class="far fa-clock mr-1"></i> {{ $meeting->meeting_date->format('d M Y, H:i') }} WIB</div>
                            @endif
                            @if($meeting->attendees)
                                <div class="mt-1"><i class="fas fa-user-friends mr-1"></i> {{ $meeting->attendees }}</div>
                            @endif
                        </div>

                        <!-- Notes / Discussion -->
                        @if($meeting->notes)
                            <div class="sticky-note-body">{{ $meeting->notes }}</div>
                        @endif

                        <!-- Action Items -->
                        @if($meeting->action_items)
                            <div class="sticky-action-box">
                                <div class="font-weight-bold mb-1">
                                    <i class="fas fa-check-square mr-1"></i> Tindak Lanjut / Action Items:
                                </div>
                                <div>{{ $meeting->action_items }}</div>
                            </div>
                        @endif

                        <!-- Footer Actions: Move Column, Change Color, Edit, Delete -->
                        <div class="sticky-card-footer">
                            <!-- Move Column Dropdown -->
                            <div class="dropdown">
                                <button class="btn btn-xs btn-light border dropdown-toggle font-weight-bold" type="button" data-toggle="dropdown">
                                    Pindah Kolom
                                </button>
                                <div class="dropdown-menu">
                                    <h6 class="dropdown-header">Pindah ke Kolom</h6>
                                    @foreach(\App\Models\Meeting::STATUSES as $sKey => $sMeta)
                                        @if($sKey !== $colKey)
                                            <a class="dropdown-item btn-move-meeting" href="javascript:void(0);" data-meeting-id="{{ $meeting->id }}" data-status="{{ $sKey }}">
                                                <i class="{{ $sMeta['icon'] }} mr-2 text-{{ $sMeta['badge'] }}"></i> {{ $sMeta['label'] }}
                                            </a>
                                        @endif
                                    @endforeach
                                </div>
                            </div>

                            <!-- Edit & Delete -->
                            <div class="btn-group">
                                <button 
                                    type="button" 
                                    class="btn btn-xs btn-light border btn-edit-meeting" 
                                    data-meeting='@json($meeting)'
                                    title="Ubah Sticky Note"
                                >
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button 
                                    type="button" 
                                    class="btn btn-xs btn-light border text-danger btn-delete-meeting" 
                                    data-meeting-id="{{ $meeting->id }}" 
                                    data-title="{{ $meeting->title }}"
                                    title="Hapus Sticky Note"
                                >
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted py-5 font-italic" style="font-size: 0.85rem;">
                        Belum ada sticky note di kolom ini.
                    </div>
                @endforelse
            </div>
        </div>
    @endforeach
</div>

<!-- ==================== MODAL TAMBAH STICKY NOTE ==================== -->
<div class="modal fade" id="modalCreateMeeting" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form method="POST" action="{{ route('meetings.store') }}" id="formCreateMeeting">
                @csrf
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title font-weight-bold">
                        <i class="fas fa-sticky-note mr-1"></i> Buat Sticky Note Meeting Baru
                    </h5>
                    <button type="button" class="close text-dark" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="create_title" class="font-weight-bold">Judul / Topik Meeting <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="create_title" class="form-control" placeholder="Contoh: Pembahasan Timeline & Fitur Modul Invoicing..." required>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label for="create_order_id" class="font-weight-bold">Order Pekerjaan</label>
                                <select name="order_id" id="create_order_id" class="form-control select2" data-placeholder="Pilih order...">
                                    <option value="">-- Tanpa Order --</option>
                                    @foreach($orders as $ord)
                                        <option value="{{ $ord->id }}">[ORD-{{ $ord->id }}] {{ $ord->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label for="create_project_id" class="font-weight-bold">Proyek Terkait</label>
                                <select name="project_id" id="create_project_id" class="form-control select2" data-placeholder="Pilih proyek...">
                                    <option value="">-- Tanpa Proyek --</option>
                                    @foreach($projects as $prj)
                                        <option value="{{ $prj->id }}">{{ $prj->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label for="create_meeting_date" class="font-weight-bold">Waktu &amp; Tanggal</label>
                                <input type="datetime-local" name="meeting_date" id="create_meeting_date" class="form-control" value="{{ now()->format('Y-m-d\TH:i') }}">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label for="create_status" class="font-weight-bold">Kolom Status <span class="text-danger">*</span></label>
                                <select name="status" id="create_status" class="form-control" required>
                                    <option value="scheduled" selected>📌 Terjadwal</option>
                                    <option value="in_progress">💬 Sedang Dibahas</option>
                                    <option value="action_needed">⚡ Tindak Lanjut</option>
                                    <option value="done">✅ Selesai &amp; Arsip</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="create_attendees" class="font-weight-bold">Peserta / Tim Hadir</label>
                        <input type="text" name="attendees" id="create_attendees" class="form-control" placeholder="Contoh: Budi, Siti (Klien), Dani (Developer)...">
                    </div>

                    <!-- Warna Sticky Note -->
                    <div class="form-group">
                        <label class="font-weight-bold d-block">Pilihan Warna Kertas Sticky Note <span class="text-danger">*</span></label>
                        <div class="d-flex align-items-center gap-3">
                            @foreach(\App\Models\Meeting::COLORS as $cKey => $cMeta)
                                <label class="mr-3 mb-0" style="cursor: pointer;">
                                    <input type="radio" name="color" value="{{ $cKey }}" class="sr-only" @checked($cKey === 'yellow')>
                                    <span class="color-swatch-radio" style="background-color: {{ $cMeta['bg'] }}; border-color: {{ $cMeta['border'] }};" title="{{ $cMeta['name'] }}"></span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="create_notes" class="font-weight-bold">Catatan / Ringkasan Pembahasan</label>
                        <textarea name="notes" id="create_notes" rows="3" class="form-control" placeholder="Tuliskan poin-poin hasil diskusi atau keputusan penting..."></textarea>
                    </div>

                    <div class="form-group">
                        <label for="create_action_items" class="font-weight-bold">Tindak Lanjut / Action Items</label>
                        <textarea name="action_items" id="create_action_items" rows="2" class="form-control" placeholder="Contoh: Dani perbaiki bug payment, Siti kirim aset logo..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning font-weight-bold text-dark">
                        <i class="fas fa-thumbtack mr-1"></i> Tempelkan Sticky Note
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ==================== MODAL EDIT STICKY NOTE ==================== -->
<div class="modal fade" id="modalEditMeeting" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form method="POST" id="formEditMeeting">
                @csrf
                @method('PUT')
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title font-weight-bold">
                        <i class="fas fa-edit mr-1"></i> Ubah Sticky Note Meeting
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="edit_title" class="font-weight-bold">Judul / Topik Meeting <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="edit_title" class="form-control" placeholder="Judul meeting..." required>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label for="edit_order_id" class="font-weight-bold">Order Pekerjaan</label>
                                <select name="order_id" id="edit_order_id" class="form-control select2">
                                    <option value="">-- Tanpa Order --</option>
                                    @foreach($orders as $ord)
                                        <option value="{{ $ord->id }}">[ORD-{{ $ord->id }}] {{ $ord->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label for="edit_project_id" class="font-weight-bold">Proyek Terkait</label>
                                <select name="project_id" id="edit_project_id" class="form-control select2">
                                    <option value="">-- Tanpa Proyek --</option>
                                    @foreach($projects as $prj)
                                        <option value="{{ $prj->id }}">{{ $prj->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label for="edit_meeting_date" class="font-weight-bold">Waktu &amp; Tanggal</label>
                                <input type="datetime-local" name="meeting_date" id="edit_meeting_date" class="form-control">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label for="edit_status" class="font-weight-bold">Kolom Status <span class="text-danger">*</span></label>
                                <select name="status" id="edit_status" class="form-control" required>
                                    <option value="scheduled">📌 Terjadwal</option>
                                    <option value="in_progress">💬 Sedang Dibahas</option>
                                    <option value="action_needed">⚡ Tindak Lanjut</option>
                                    <option value="done">✅ Selesai &amp; Arsip</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="edit_attendees" class="font-weight-bold">Peserta / Tim Hadir</label>
                        <input type="text" name="attendees" id="edit_attendees" class="form-control" placeholder="Daftar peserta...">
                    </div>

                    <!-- Warna Sticky Note -->
                    <div class="form-group">
                        <label class="font-weight-bold d-block">Pilihan Warna Kertas Sticky Note <span class="text-danger">*</span></label>
                        <div class="d-flex align-items-center gap-3">
                            @foreach(\App\Models\Meeting::COLORS as $cKey => $cMeta)
                                <label class="mr-3 mb-0" style="cursor: pointer;">
                                    <input type="radio" name="color" value="{{ $cKey }}" id="edit_color_{{ $cKey }}" class="sr-only">
                                    <span class="color-swatch-radio" style="background-color: {{ $cMeta['bg'] }}; border-color: {{ $cMeta['border'] }};" title="{{ $cMeta['name'] }}"></span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="edit_notes" class="font-weight-bold">Catatan / Ringkasan Pembahasan</label>
                        <textarea name="notes" id="edit_notes" rows="3" class="form-control" placeholder="Poin pembahasan..."></textarea>
                    </div>

                    <div class="form-group">
                        <label for="edit_action_items" class="font-weight-bold">Tindak Lanjut / Action Items</label>
                        <textarea name="action_items" id="edit_action_items" rows="2" class="form-control" placeholder="Action items..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary font-weight-bold">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    $(function () {
        // 1. Move Meeting Sticky Note to another Column via Ajax
        $(document).on('click', '.btn-move-meeting', function () {
            const meetingId = $(this).data('meeting-id');
            const targetStatus = $(this).data('status');

            $.ajax({
                url: `/meetings/${meetingId}/status`,
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    status: targetStatus
                },
                success: function (res) {
                    if (res.success) {
                        location.reload();
                    }
                },
                error: function (xhr) {
                    alert('Gagal memindahkan sticky note: ' + (xhr.responseJSON?.message || 'Terjadi kesalahan'));
                }
            });
        });

        // 2. Open Edit Modal with populated data
        $(document).on('click', '.btn-edit-meeting', function () {
            const meeting = $(this).data('meeting');

            $('#formEditMeeting').attr('action', `/meetings/${meeting.id}`);
            $('#edit_title').val(meeting.title || '');
            $('#edit_order_id').val(meeting.order_id || '').trigger('change');
            $('#edit_project_id').val(meeting.project_id || '').trigger('change');

            if (meeting.meeting_date) {
                // Format YYYY-MM-DDTHH:MM
                const dt = new Date(meeting.meeting_date);
                const localIso = new Date(dt.getTime() - (dt.getTimezoneOffset() * 60000)).toISOString().slice(0, 16);
                $('#edit_meeting_date').val(localIso);
            } else {
                $('#edit_meeting_date').val('');
            }

            $('#edit_status').val(meeting.status || 'scheduled');
            $('#edit_attendees').val(meeting.attendees || '');
            $('#edit_notes').val(meeting.notes || '');
            $('#edit_action_items').val(meeting.action_items || '');

            $(`input[name="color"][id="edit_color_${meeting.color}"]`).prop('checked', true);

            $('#modalEditMeeting').modal('show');
        });

        // 3. Delete Meeting Sticky Note
        $(document).on('click', '.btn-delete-meeting', function () {
            const meetingId = $(this).data('meeting-id');
            const title = $(this).data('title');

            if (!confirm(`Apakah Anda yakin ingin menghapus catatan meeting '${title}'?`)) {
                return;
            }

            $.ajax({
                url: `/meetings/${meetingId}`,
                method: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function (res) {
                    location.reload();
                },
                error: function (xhr) {
                    alert('Gagal menghapus catatan meeting: ' + (xhr.responseJSON?.message || 'Terjadi kesalahan'));
                }
            });
        });
    });
</script>
@endsection
