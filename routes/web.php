<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseCategoryController;
use App\Http\Controllers\PlatformController;
use App\Http\Controllers\ServicePackageController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\WithdrawalController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\DocumentTemplateController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\AgreementController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\DataTableController;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('platforms', PlatformController::class)->except('show');
    Route::get('/platforms/{platform}/calculate', [PlatformController::class, 'calculate'])->name('platforms.calculate');
    Route::get('/platforms/{platform}/fee-rules', [PlatformController::class, 'feeRules'])->name('platforms.fee-rules');
    Route::post('/platforms/{platform}/fee-rules', [PlatformController::class, 'storeFeeRule'])->name('platforms.fee-rules.store');
    Route::delete('/platforms/{platform}/fee-rules/{feeRule}', [PlatformController::class, 'destroyFeeRule'])->name('platforms.fee-rules.destroy');
    Route::resource('clients', ClientController::class);
    Route::resource('service-packages', ServicePackageController::class)->except('show');
    Route::resource('expense-categories', ExpenseCategoryController::class)->except(['show', 'create', 'edit']);
    Route::resource('orders', OrderController::class)->only(['index', 'create', 'store', 'edit', 'update']);
    Route::patch('/orders/{order}/payout', [OrderController::class, 'updatePayout'])->name('orders.payout');
    Route::resource('withdrawals', WithdrawalController::class)->only(['index', 'create', 'store']);
    Route::resource('payments', PaymentController::class)->only(['index', 'create', 'store']);
    Route::resource('document-templates', DocumentTemplateController::class)->except('show');
    Route::resource('invoices', InvoiceController::class)->only(['index', 'create', 'store', 'show']);
    Route::get('/invoices/{invoice}/download', [InvoiceController::class, 'download'])->name('invoices.download');
    Route::resource('agreements', AgreementController::class)->only(['index', 'create', 'store', 'show', 'update']);
    Route::post('/agreements/{agreement}/send', [AgreementController::class, 'send'])->name('agreements.send');
    Route::post('/agreements/{agreement}/sign', [AgreementController::class, 'sign'])->name('agreements.sign');
    Route::get('/agreement-versions/{agreement_version}/{format}', [AgreementController::class, 'download'])->whereIn('format', ['pdf', 'docx'])->name('agreement-versions.download');
    Route::resource('subscriptions', SubscriptionController::class)->except('show');
    Route::post('/subscriptions/{subscription}/renew', [SubscriptionController::class, 'renew'])->name('subscriptions.renew');
    Route::post('/subscriptions/{subscription}/freeze', [SubscriptionController::class, 'freeze'])->name('subscriptions.freeze');
    Route::resource('projects', ProjectController::class);
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');
    Route::get('/datatable/orders', [DataTableController::class, 'orders'])->name('datatable.orders');
    Route::get('/datatable/invoices', [DataTableController::class, 'invoices'])->name('datatable.invoices');
    Route::get('/datatable/clients', [DataTableController::class, 'clients'])->name('datatable.clients');
    Route::get('/datatable/payments', [DataTableController::class, 'payments'])->name('datatable.payments');
    Route::get('/datatable/agreements', [DataTableController::class, 'agreements'])->name('datatable.agreements');
    Route::get('/datatable/subscriptions', [DataTableController::class, 'subscriptions'])->name('datatable.subscriptions');
    Route::get('/datatable/projects', [DataTableController::class, 'projects'])->name('datatable.projects');
    Route::get('/datatable/withdrawals', [DataTableController::class, 'withdrawals'])->name('datatable.withdrawals');
    Route::get('/datatable/service-packages', [DataTableController::class, 'servicePackages'])->name('datatable.service-packages');
    Route::get('/datatable/platforms', [DataTableController::class, 'platforms'])->name('datatable.platforms');
    Route::get('/datatable/expense-categories', [DataTableController::class, 'expenseCategories'])->name('datatable.expense-categories');
    Route::get('/datatable/document-templates', [DataTableController::class, 'documentTemplates'])->name('datatable.document-templates');
    Route::get('/datatable/reports', [DataTableController::class, 'reports'])->name('datatable.reports');
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
});
