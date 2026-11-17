<?php

return [
    [
        'text' => 'Dashboard',
        'route' => 'dashboard',
        'icon' => 'fas fa-tachometer-alt',
        'active' => ['dashboard'],
    ],
    [
        'header' => 'PEKERJAAN & KEUANGAN',
    ],
    [
        'text' => 'Order Pekerjaan',
        'route' => 'orders.index',
        'icon' => 'fas fa-clipboard-list',
        'active' => ['orders.*'],
    ],
    [
        'text' => 'Penarikan Saldo',
        'route' => 'withdrawals.index',
        'icon' => 'fas fa-wallet',
        'active' => ['withdrawals.*'],
    ],
    [
        'text' => 'Invoice',
        'route' => 'invoices.index',
        'icon' => 'fas fa-file-invoice-dollar',
        'active' => ['invoices.*'],
    ],
    [
        'text' => 'Perjanjian Kerjasama',
        'route' => 'agreements.index',
        'icon' => 'fas fa-file-contract',
        'active' => ['agreements.*'],
    ],
    [
        'text' => 'Langganan & Masa Aktif',
        'route' => 'subscriptions.index',
        'icon' => 'fas fa-clock',
        'active' => ['subscriptions.*'],
    ],
    [
        'text' => 'Pembayaran Masuk',
        'route' => 'payments.index',
        'icon' => 'fas fa-money-bill-wave',
        'active' => ['payments.*'],
    ],
    [
        'text' => 'Pengeluaran',
        'route' => 'expenses.index',
        'icon' => 'fas fa-receipt',
        'active' => ['expenses.*'],
        'soon' => true,
    ],
    [
        'text' => 'Proyek & Progress',
        'route' => 'projects.index',
        'icon' => 'fas fa-tasks',
        'active' => ['projects.*'],
    ],
    [
        'text' => 'Laporan',
        'route' => 'reports.index',
        'icon' => 'fas fa-chart-bar',
        'active' => ['reports.*'],
    ],
    [
        'header' => 'MASTER DATA',
    ],
    [
        'text' => 'Platform',
        'route' => 'platforms.index',
        'icon' => 'fas fa-layer-group',
        'active' => ['platforms.*'],
    ],
    [
        'text' => 'Klien',
        'route' => 'clients.index',
        'icon' => 'fas fa-users',
        'active' => ['clients.*'],
    ],
    [
        'text' => 'Paket Layanan',
        'route' => 'service-packages.index',
        'icon' => 'fas fa-box-open',
        'active' => ['service-packages.*'],
    ],
    [
        'text' => 'Kategori Pengeluaran',
        'route' => 'expense-categories.index',
        'icon' => 'fas fa-tags',
        'active' => ['expense-categories.*'],
    ],
    [
        'header' => 'SISTEM',
    ],
    [
        'text' => 'Pengaturan Usaha',
        'route' => 'settings.index',
        'icon' => 'fas fa-cogs',
        'active' => ['settings.*'],
    ],
    [
        'text' => 'Template Dokumen',
        'route' => 'document-templates.index',
        'icon' => 'fas fa-file-alt',
        'active' => ['document-templates.*'],
    ],
];
