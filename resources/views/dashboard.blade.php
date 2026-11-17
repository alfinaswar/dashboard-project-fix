@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page_title', 'Ringkasan Sistem')

@section('content')
<div class="row">
    @foreach([
        ['monthlyNet', 'Pendapatan Bersih Bulan Ini', 'success', 'orders.index'],
        ['monthlyFees', 'Potongan Platform Bulan Ini', 'warning', 'orders.index'],
        ['balancePending', 'Saldo Fastwork Belum Ditarik', 'info', 'orders.index'],
        ['unpaidInvoices', 'Tagihan Belum Lunas', 'danger', 'invoices.index'],
    ] as [$metric, $label, $color, $route])
        <div class="col-lg-3 col-6"><div class="small-box bg-white border shadow-sm"><div class="inner"><h3 class="text-{{ $color }} font-weight-bold">{{ format_rupiah($$metric) }}</h3><p class="text-muted mb-0">{{ $label }}</p></div><a href="{{ route($route) }}" class="small-box-footer text-{{ $color }} font-weight-bold bg-light">Lihat detail <i class="fas fa-arrow-circle-right"></i></a></div></div>
    @endforeach
</div>
<div class="row">
    <div class="col-lg-4"><div class="small-box bg-white border shadow-sm"><div class="inner"><h3 class="text-primary font-weight-bold">{{ $expiringSubscriptions }}</h3><p class="text-muted mb-0">Langganan Berakhir 30 Hari</p></div><a href="{{ route('subscriptions.index') }}" class="small-box-footer text-primary font-weight-bold bg-light">Lihat langganan <i class="fas fa-arrow-circle-right"></i></a></div></div>
    <div class="col-lg-8"><div class="card card-outline card-primary shadow-sm"><div class="card-header"><h3 class="card-title font-weight-bold">Proyek Aktif</h3></div><div class="card-body p-0"><table class="table table-sm mb-0"><thead><tr><th>Proyek</th><th>Klien</th><th>Progress</th></tr></thead><tbody>@forelse($activeProjects as $project)<tr><td><a href="{{ route('projects.show',$project) }}">{{ $project->name }}</a></td><td>{{ $project->client->name }}</td><td><div class="progress"><div class="progress-bar" style="width:{{ $project->progress_percent }}%">{{ $project->progress_percent }}%</div></div></td></tr>@empty<tr><td colspan="3" class="text-center text-muted">Belum ada proyek aktif.</td></tr>@endforelse</tbody></table></div></div></div>
</div>
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-white border shadow-sm">
            <div class="inner">
                <h3 class="text-primary font-weight-bold">{{ $platformCount }}</h3>
                <p class="text-muted mb-0">Platform Sumber</p>
            </div>
            <div class="icon">
                <i class="fas fa-layer-group text-muted" style="opacity: 0.3;"></i>
            </div>
            <a href="{{ route('platforms.index') }}" class="small-box-footer text-primary font-weight-bold bg-light">
                Kelola Platform <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-white border shadow-sm">
            <div class="inner">
                <h3 class="text-success font-weight-bold">{{ $clientCount }}</h3>
                <p class="text-muted mb-0">Total Klien Terdaftar</p>
            </div>
            <div class="icon">
                <i class="fas fa-users text-muted" style="opacity: 0.3;"></i>
            </div>
            <a href="{{ route('clients.index') }}" class="small-box-footer text-success font-weight-bold bg-light">
                Kelola Klien <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-white border shadow-sm">
            <div class="inner">
                <h3 class="text-info font-weight-bold">{{ $packageCount }}</h3>
                <p class="text-muted mb-0">Paket Layanan</p>
            </div>
            <div class="icon">
                <i class="fas fa-box-open text-muted" style="opacity: 0.3;"></i>
            </div>
            <a href="{{ route('service-packages.index') }}" class="small-box-footer text-info font-weight-bold bg-light">
                Kelola Paket <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-white border shadow-sm">
            <div class="inner">
                <h3 class="text-warning font-weight-bold">{{ $categoryCount }}</h3>
                <p class="text-muted mb-0">Kategori Pengeluaran</p>
            </div>
            <div class="icon">
                <i class="fas fa-tags text-muted" style="opacity: 0.3;"></i>
            </div>
            <a href="{{ route('expense-categories.index') }}" class="small-box-footer text-warning font-weight-bold bg-light">
                Kelola Kategori <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
</div>

<div class="row mt-3">
    <div class="col-lg-8">
        <div class="card card-outline card-primary shadow-sm">
            <div class="card-header bg-white border-bottom-0">
                <h3 class="card-title font-weight-bold">
                    <i class="fas fa-user-plus mr-1 text-primary"></i> Klien Terbaru
                </h3>
                <div class="card-tools">
                    <a href="{{ route('clients.create') }}" class="btn btn-sm btn-primary font-weight-bold">
                        <i class="fas fa-plus mr-1"></i> Tambah Klien
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead>
                            <tr class="text-muted text-uppercase small">
                                <th>Nama Klien</th>
                                <th>Platform</th>
                                <th>Kontak / Email</th>
                                <th>No. Telepon</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentClients as $client)
                                <tr>
                                    <td class="font-weight-bold">
                                        <a href="{{ route('clients.show', $client) }}" class="text-dark">
                                            {{ $client->name }}
                                        </a>
                                        @if($client->company_name)
                                            <div class="text-muted small">{{ $client->company_name }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-info">{{ $client->platform->name ?? 'Langsung' }}</span>
                                    </td>
                                    <td>{{ $client->email ?? '-' }}</td>
                                    <td>{{ $client->phone ?? '-' }}</td>
                                    <td class="text-right">
                                        <a href="{{ route('clients.show', $client) }}" class="btn btn-xs btn-outline-primary" title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('clients.edit', $client) }}" class="btn btn-xs btn-outline-secondary" title="Ubah">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        <i class="fas fa-users-slash fa-2x mb-2 d-block"></i>
                                        Belum ada data klien. Silakan tambah klien pertama Anda.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white text-right">
                <a href="{{ route('clients.index') }}" class="text-primary font-weight-bold small">
                    Lihat Semua Klien <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card card-outline card-info shadow-sm">
            <div class="card-header bg-white border-bottom-0">
                <h3 class="card-title font-weight-bold">
                    <i class="fas fa-info-circle mr-1 text-info"></i> Status Fase Pengerjaan
                </h3>
            </div>
            <div class="card-body">
                <div class="callout callout-success mb-3">
                    <h6 class="font-weight-bold text-success mb-1">
                        <i class="fas fa-check-circle mr-1"></i> Sistem siap digunakan
                    </h6>
                    <p class="small text-muted mb-0">
                        Auth, master data, order, invoice, perjanjian, langganan, proyek, dashboard, laporan, dan notifikasi tersedia.
                    </p>
                </div>
                <div class="callout callout-info mb-3">
                    <h6 class="font-weight-bold text-info mb-1">
                        <i class="fas fa-clock mr-1"></i> Fase 8: Verifikasi
                    </h6>
                    <p class="small text-muted mb-0">
                        Jalankan scheduler harian dan backup database sebelum deploy produksi.
                    </p>
                </div>
                <a href="{{ route('settings.index') }}" class="btn btn-outline-secondary btn-block btn-sm">
                    <i class="fas fa-sliders-h mr-1"></i> Cek Pengaturan Bisnis
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
