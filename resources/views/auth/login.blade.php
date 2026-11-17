<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk | {{ setting('company_name', config('app.name')) }}</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/fontawesome-free/css/all.min.css') }}">
    <!-- Theme style -->
    <link rel="stylesheet" href="{{ asset('assets/dist/css/adminlte.min.css') }}">
</head>
<body class="hold-transition login-page bg-light">
<div class="login-box">
    <div class="login-logo mb-3">
        <a href="/" class="font-weight-bold text-dark">
            <i class="fas fa-chart-pie text-primary mr-1"></i>
            {{ setting('company_name', 'Tracker Keuangan') }}
        </a>
    </div>
    <!-- /.login-logo -->
    <div class="card shadow-sm border-0">
        <div class="card-body login-card-body p-4">
            <p class="login-box-msg text-muted pb-2">Silakan masuk untuk mengelola sistem</p>

            @php
                // Notifikasi tampil lewat SweetAlert2, bukan alert inline.
                $sweetAlerts = [];

                if (session('error')) {
                    $sweetAlerts[] = ['type' => 'error', 'title' => 'Gagal masuk', 'text' => session('error')];
                }

                if ($errors->any()) {
                    $sweetAlerts[] = [
                        'type' => 'error',
                        'title' => 'Data belum lengkap',
                        'text' => 'Periksa kembali isian berikut:',
                        'list' => array_values(array_unique($errors->all())),
                    ];
                }
            @endphp

            @if($sweetAlerts)
                <div id="sweet-alerts" data-alerts="{{ json_encode($sweetAlerts) }}" hidden></div>
            @endif

            <form action="{{ route('login') }}" method="post">
                @csrf
                <div class="input-group mb-3">
                    <input type="email" name="email" value="{{ old('email') }}" class="form-control" placeholder="Email" required autofocus>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-envelope"></span>
                        </div>
                    </div>
                </div>

                <div class="input-group mb-3">
                    <input type="password" name="password" class="form-control" placeholder="Kata Sandi" required>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-lock"></span>
                        </div>
                    </div>
                </div>

                <div class="row align-items-center mb-3">
                    <div class="col-8">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" name="remember" class="custom-control-input" id="remember" {{ old('remember') ? 'checked' : '' }}>
                            <label class="custom-control-label font-weight-normal text-muted" for="remember">
                                Ingat saya
                            </label>
                        </div>
                    </div>
                    <!-- /.col -->
                    <div class="col-4">
                        <button type="submit" class="btn btn-primary btn-block font-weight-bold">
                            Masuk
                        </button>
                    </div>
                    <!-- /.col -->
                </div>
            </form>
        </div>
        <!-- /.login-card-body -->
    </div>
</div>
<!-- /.login-box -->

<!-- jQuery -->
<script src="{{ asset('assets/plugins/jquery/jquery.min.js') }}"></script>
<!-- Bootstrap 4 -->
<script src="{{ asset('assets/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<!-- SweetAlert2 -->
<script src="{{ asset('assets/plugins/sweetalert2/sweetalert2.min.js') }}"></script>
<!-- AdminLTE App -->
<script src="{{ asset('assets/dist/js/adminlte.min.js') }}"></script>

<script>
    // Tampilkan notifikasi dari server lewat SweetAlert2.
    function showSweetAlerts() {
        const holder = document.getElementById('sweet-alerts');
        if (!holder || typeof Swal === 'undefined') return;

        let alerts = [];
        try { alerts = JSON.parse(holder.dataset.alerts || '[]'); } catch (e) { return; }

        alerts.forEach(function (alert) {
            const isList = Array.isArray(alert.list) && alert.list.length > 0;
            Swal.fire({
                icon: alert.type || 'info',
                title: alert.title || '',
                html: isList
                    ? '<p class="mb-2">' + alert.text + '</p><ul class="text-left pl-4 mb-0">' +
                      alert.list.map(function (msg) { return '<li>' + msg + '</li>'; }).join('') + '</ul>'
                    : alert.text || '',
                confirmButtonText: 'Mengerti',
                confirmButtonColor: alert.type === 'error' ? '#dc3545' : '#28a745',
            });
        });
    }

    showSweetAlerts();
</script>
</body>
</html>
