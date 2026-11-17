<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Cetak') | {{ setting('company_name', config('app.name')) }}</title>
    <link rel="stylesheet" href="{{ asset('assets/plugins/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/dist/css/adminlte.min.css') }}">
    <style>
        body { background: #f1f5f9; }
        .print-toolbar { padding: 12px 24px; background: #fff; border-bottom: 1px solid #e2e8f0; }
        .invoice-sheet { background: #fff; max-width: 800px; margin: 24px auto; padding: 40px; }
        @media print {
            body { background: #fff; }
            .invoice-sheet { max-width: none; margin: 0; padding: 0; }
            @page { margin: 18mm; }
        }
    </style>
</head>
<body>
    @yield('content')
</body>
</html>
