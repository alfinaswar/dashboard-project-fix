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
        'text' => 'Pembayaran Masuk',
        'route' => 'payments.index',
        'icon' => 'fas fa-money-bill-wave',
        'hidden' => true,
        'active' => ['payments.*'],
    ],
    [
        'text' => 'Langganan & Masa Aktif',
        'route' => 'subscriptions.index',
        'icon' => 'fas fa-clock',
        'active' => ['subscriptions.*'],
    ],
    [
        'text' => 'Pengeluaran',
        'route' => 'expenses.index',
        'icon' => 'fas fa-receipt',
        'active' => ['expenses.*'],
        'soon' => true,
        'hidden' => true,
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
        'text' => 'Master Jenis Order',
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
