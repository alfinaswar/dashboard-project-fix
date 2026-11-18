@extends('layouts.admin')

@section('title', 'Proyek & Progress')
@section('page_title', 'Proyek & Progress')

@section('page_actions')
<div class="btn-group mr-2">
    <a href="{{ route('projects.index', ['view' => 'kanban']) }}" class="btn {{ $viewMode === 'kanban' ? 'btn-primary' : 'btn-outline-primary' }}">
        <i class="fas fa-columns mr-1"></i> Kanban Board
    </a>
    <a href="{{ route('projects.index', ['view' => 'table']) }}" class="btn {{ $viewMode === 'table' ? 'btn-primary' : 'btn-outline-primary' }}">
        <i class="fas fa-list mr-1"></i> Tabel Daftar
    </a>
</div>
<a href="{{ route('meetings.index') }}" class="btn btn-outline-warning mr-2">
    <i class="fas fa-sticky-note mr-1"></i> Catatan Meeting
</a>
<a href="{{ route('projects.create') }}" class="btn btn-primary">
    <i class="fas fa-plus mr-1"></i> Tambah Proyek
</a>
@endsection

@section('styles')
<style>
    .kanban-board-container {
        display: flex;
        gap: 1.25rem;
        overflow-x: auto;
        padding-bottom: 1.5rem;
        min-height: calc(100vh - 220px);
    }
    .kanban-col {
        flex: 1;
        min-width: 290px;
        max-width: 360px;
        background-color: #F1F5F9;
        border-radius: 12px;
        display: flex;
        flex-direction: column;
        border: 1px solid #E2E8F0;
    }
    .kanban-col-header {
        padding: 0.9rem 1.1rem;
        border-bottom: 2px solid transparent;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-top-left-radius: 12px;
        border-top-right-radius: 12px;
        font-weight: 700;
        font-size: 0.92rem;
    }
    .col-planning { border-top: 4px solid #64748B; background-color: #FFFFFF; }
    .col-running { border-top: 4px solid #3B82F6; background-color: #FFFFFF; }
    .col-on_hold { border-top: 4px solid #F59E0B; background-color: #FFFFFF; }
    .col-done { border-top: 4px solid #10B981; background-color: #FFFFFF; }

    .kanban-cards-list {
        padding: 0.85rem;
        flex: 1;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        gap: 0.85rem;
    }
    .project-card {
        background: #FFFFFF;
        border-radius: 10px;
        padding: 1.1rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
        border: 1px solid #E2E8F0;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
        text-decoration: none !important;
        color: inherit;
        display: block;
    }
    .project-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 12px -2px rgba(0,0,0,0.1);
        color: inherit;
    }
    .project-card-title {
        font-size: 1rem;
        font-weight: 700;
        color: #0F172A;
        margin-bottom: 0.4rem;
        line-height: 1.3;
    }
    .order-pill {
        display: inline-block;
        background-color: #EFF6FF;
        color: #1D4ED8;
        border: 1px solid #DBEAFE;
        font-size: 0.73rem;
        font-weight: 700;
        padding: 2px 7px;
        border-radius: 5px;
        margin-bottom: 0.5rem;
    }
    .card-meta-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 0.78rem;
        color: #64748B;
        margin-top: 0.75rem;
        padding-top: 0.6rem;
        border-top: 1px dashed #E2E8F0;
    }
</style>
@endsection

@section('content')

@if($viewMode === 'kanban')
    <!-- ==================== KANBAN BOARD VIEW ==================== -->
    <div class="kanban-board-container">
        @php
            $colDefinitions = [
                'planning' => ['title' => 'Rencana / Planning', 'icon' => 'fas fa-clipboard-list', 'color' => 'secondary', 'class' => 'col-planning'],
                'running' => ['title' => 'Sedang Berjalan / In Progress', 'icon' => 'fas fa-play-circle', 'color' => 'primary', 'class' => 'col-running'],
                'on_hold' => ['title' => 'Ditunda / On Hold', 'icon' => 'fas fa-pause-circle', 'color' => 'warning', 'class' => 'col-on_hold'],
                'done' => ['title' => 'Selesai / Done', 'icon' => 'fas fa-check-circle', 'color' => 'success', 'class' => 'col-done'],
            ];
        @endphp

        @foreach($colDefinitions as $stKey => $def)
            @php
                $items = $kanbanProjects[$stKey] ?? collect();
            @endphp
            <div class="kanban-col">
                <div class="kanban-col-header {{ $def['class'] }}">
                    <span>
                        <i class="{{ $def['icon'] }} mr-1 text-{{ $def['color'] }}"></i> {{ $def['title'] }}
                    </span>
                    <span class="badge badge-{{ $def['color'] }} badge-pill">{{ $items->count() }}</span>
                </div>

                <div class="kanban-cards-list">
                    @forelse($items as $proj)
                        <div class="project-card">
                            @if($proj->order)
                                <div class="order-pill">
                                    <i class="fas fa-file-invoice mr-1"></i> ORD-{{ $proj->order->id }}: {{ Str::limit($proj->order->title, 25) }}
                                </div>
                            @endif

                            <a href="{{ route('projects.show', $proj) }}" class="project-card-title d-block">
                                {{ $proj->name }}
                            </a>

                            <div class="small text-muted mb-2">
                                <i class="far fa-user mr-1"></i> {{ $proj->order?->client?->name ?? $proj->client?->name ?? '-' }}
                            </div>

                            <!-- Progress Bar -->
                            <div class="d-flex justify-content-between align-items-center small mb-1">
                                <span class="font-weight-bold text-muted">Progress</span>
                                <span class="font-weight-bold {{ $proj->progress_percent >= 100 ? 'text-success' : 'text-primary' }}">
                                    {{ $proj->progress_percent }}%
                                </span>
                            </div>
                            <div class="progress" style="height: 8px; border-radius: 99px;">
                                <div 
                                    class="progress-bar {{ $proj->progress_percent >= 100 ? 'bg-success' : 'bg-primary' }}" 
                                    style="width: {{ $proj->progress_percent }}%"
                                ></div>
                            </div>

                            <div class="card-meta-row">
                                <div>
                                    <i class="far fa-calendar-alt mr-1"></i> 
                                    {{ $proj->target_date ? $proj->target_date->format('d/m/Y') : '-' }}
                                </div>
                                <div>
                                    <span class="badge badge-light border" title="Jumlah Tahapan / Tugas">
                                        <i class="fas fa-layer-group text-muted mr-1"></i> {{ $proj->stages->count() }}
                                    </span>
                                    @if($proj->meetings->count() > 0)
                                        <span class="badge badge-light border text-warning ml-1" title="Catatan Meeting">
                                            <i class="fas fa-sticky-note mr-1"></i> {{ $proj->meetings->count() }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="mt-2 text-right">
                                <a href="{{ route('projects.show', $proj) }}" class="btn btn-xs btn-outline-primary" title="Buka Kanban Progress">
                                    <i class="fas fa-columns mr-1"></i> Kanban Progress
                                </a>
                                <a href="{{ route('projects.edit', $proj) }}" class="btn btn-xs btn-outline-secondary" title="Ubah Proyek">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-4 font-italic" style="font-size: 0.85rem;">
                            Tidak ada proyek pada status ini.
                        </div>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>

@else
    <!-- ==================== TABLE LIST VIEW ==================== -->
    <div class="card card-outline card-primary shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table id="projects-table" class="table table-hover w-100">
                    <thead class="thead-light">
                        <tr>
                            <th style="min-width: 180px;">Order Pekerjaan</th>
                            <th>Nama Proyek</th>
                            <th>Klien</th>
                            <th>Status Proyek</th>
                            <th style="min-width: 130px;">Progress</th>
                            <th>Target Selesai</th>
                            <th style="width: 120px;">Aksi</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
@endif

@endsection

@section('scripts')
@if($viewMode === 'table')
<script>
    $(function () {
        $('#projects-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route('datatable.projects') }}',
            language: dtLanguage,
            order: [[0, 'desc']],
            columns: [
                { data: 'order_title', name: 'order.title', orderable: false },
                { data: 'name', name: 'name' },
                { data: 'client_name', name: 'client.name', orderable: false },
                { data: 'status', name: 'status' },
                { data: 'progress', name: 'progress_percent', orderable: false },
                { data: 'target_date', name: 'target_date' },
                { data: 'actions', name: 'actions', orderable: false, searchable: false }
            ]
        });
    });
</script>
@endif
@endsection
