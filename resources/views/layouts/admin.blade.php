<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard') | {{ setting('company_name', config('app.name')) }}</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/fontawesome-free/css/all.min.css') }}">
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css') }}">
    <!-- Theme style (AdminLTE 3 Light) -->
    <link rel="stylesheet" href="{{ asset('assets/dist/css/adminlte.min.css') }}">

    <style>
        .content-wrapper { background-color: #f8fafc; }
        .main-sidebar { border-right: 1px solid #e2e8f0; }
        .card { box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.08); border: none; }
        .table thead th { border-top: none; }
    </style>

    @yield('styles')
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light border-bottom-0">
        <!-- Left navbar links -->
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                <a href="{{ route('dashboard') }}" class="nav-link font-weight-bold">
                    {{ setting('company_name', 'Tracker Keuangan & Klien') }}
                </a>
            </li>
        </ul>

        <!-- Right navbar links -->
        <ul class="navbar-nav ml-auto">
            <!-- User Dropdown Menu -->
            <li class="nav-item dropdown">
                <a class="nav-link" data-toggle="dropdown" href="#">
                    <i class="far fa-user-circle mr-1"></i>
                    <span class="font-weight-bold">{{ auth()->user()->name ?? 'Admin' }}</span>
                    <i class="fas fa-caret-down ml-1"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right">
                    <span class="dropdown-item dropdown-header">{{ auth()->user()->email ?? '' }}</span>
                    <div class="dropdown-divider"></div>
                    <a href="{{ route('settings.index') }}" class="dropdown-item">
                        <i class="fas fa-cogs mr-2"></i> Pengaturan
                    </a>
                    <div class="dropdown-divider"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item text-danger">
                            <i class="fas fa-sign-out-alt mr-2"></i> Keluar
                        </button>
                    </form>
                </div>
            </li>
        </ul>
    </nav>
    <!-- /.navbar -->

    <!-- Main Sidebar Container -->
    <aside class="main-sidebar sidebar-light-primary elevation-1">
        <!-- Brand Logo -->
        <a href="{{ route('dashboard') }}" class="brand-link text-center text-primary font-weight-bold">
            <i class="fas fa-chart-line mr-1"></i>
            <span class="brand-text">{{ setting('company_name', 'Tracker') }}</span>
        </a>

        <!-- Sidebar -->
        <div class="sidebar">
            <!-- Sidebar Menu -->
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column nav-flat" data-widget="treeview" role="menu" data-accordion="false">
                    @foreach(config('menu', []) as $item)
                        @if(isset($item['header']))
                            <li class="nav-header text-uppercase font-weight-bold text-muted small mt-2">{{ $item['header'] }}</li>
                        @else
                            @php
                                $isActive = false;
                                if (isset($item['active'])) {
                                    foreach ($item['active'] as $pattern) {
                                        if (request()->routeIs($pattern)) {
                                            $isActive = true;
                                            break;
                                        }
                                    }
                                }
                                $isSoon = !empty($item['soon']) && (!Route::has($item['route']) || $item['route'] === '#');
                                $url = Route::has($item['route']) ? route($item['route']) : '#';
                            @endphp
                            <li class="nav-item">
                                <a href="{{ $url }}" class="nav-link {{ $isActive ? 'active' : '' }} {{ $isSoon ? 'text-muted' : '' }}">
                                    <i class="nav-icon {{ $item['icon'] ?? 'fas fa-circle' }}"></i>
                                    <p>
                                        {{ $item['text'] }}
                                        @if($isSoon)
                                            <span class="badge badge-light right border text-muted">Fase Berikutnya</span>
                                        @endif
                                    </p>
                                </a>
                            </li>
                        @endif
                    @endforeach
                </ul>
            </nav>
            <!-- /.sidebar-menu -->
        </div>
        <!-- /.sidebar -->
    </aside>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2 align-items-center">
                    <div class="col-sm-6">
                        <h1 class="m-0 text-dark font-weight-bold">@yield('page_title', 'Halaman')</h1>
                    </div>
                    <div class="col-sm-6 text-sm-right">
                        @yield('page_actions')
                    </div>
                </div>
            </div>
        </div>
        <!-- /.content-header -->

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                        <i class="icon fas fa-check mr-2"></i> {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                        <i class="icon fas fa-ban mr-2"></i> {{ session('error') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                        <h6 class="font-weight-bold mb-1"><i class="icon fas fa-exclamation-triangle mr-2"></i> Terdapat kesalahan pengisian data:</h6>
                        <ul class="mb-0 pl-3">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                @yield('content')
            </div>
        </section>
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->

    <footer class="main-footer text-sm text-muted">
        <div class="float-right d-none d-sm-inline">
            Sistem Pencatatan Keuangan & Klien v1.0
        </div>
        <strong>&copy; {{ date('Y') }} {{ setting('company_name', 'Studio Freelance') }}.</strong> All rights reserved.
    </footer>
</div>
<!-- ./wrapper -->

<!-- REQUIRED SCRIPTS -->
<!-- jQuery -->
<script src="{{ asset('assets/plugins/jquery/jquery.min.js') }}"></script>
<!-- Bootstrap 4 -->
<script src="{{ asset('assets/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<!-- Select2 -->
    <script src="{{ asset('assets/plugins/select2/js/select2.full.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<!-- SweetAlert2 -->
<script src="{{ asset('assets/plugins/sweetalert2/sweetalert2.min.js') }}"></script>
<!-- AdminLTE App -->
<script src="{{ asset('assets/dist/js/adminlte.min.js') }}"></script>

<script>
    const dtLanguage = {
        emptyTable: 'Belum ada data.', search: 'Cari:', lengthMenu: 'Tampilkan _MENU_',
        info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
        paginate: { next: 'Berikutnya', previous: 'Sebelumnya' }
    };

    $(function () {
        // Initialize Select2 Elements
        $('.select2').select2({
            theme: 'bootstrap4'
        });

        // Auto close alerts after 5 seconds
        setTimeout(function() {
            $(".alert:not(.alert-danger)").fadeOut('slow');
        }, 5000);
    });
</script>

@yield('scripts')
</body>
</html>
