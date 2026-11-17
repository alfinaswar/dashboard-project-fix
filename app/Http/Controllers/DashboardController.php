<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ExpenseCategory;
use App\Models\Platform;
use App\Models\ServicePackage;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Project;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display dashboard.
     */
    public function index()
    {
        $month = Carbon::now();
        $platformCount = Platform::count();
        $clientCount = Client::count();
        $packageCount = ServicePackage::count();
        $categoryCount = ExpenseCategory::count();

        $recentClients = Client::with('platform')->latest()->take(5)->get();
        $monthlyOrders = Order::where('status', 'completed')
            ->whereYear('completed_at', $month->year)
            ->whereMonth('completed_at', $month->month);
        $monthlyNet = (clone $monthlyOrders)->sum('net_amount');
        $monthlyFees = (clone $monthlyOrders)->sum('platform_fee');
        $balancePending = Order::where('payout_status', 'in_balance')->sum('net_amount');
        $unpaidInvoices = Invoice::whereIn('status', ['draft', 'sent', 'unpaid', 'partial'])->sum('total');
        $expiringSubscriptions = Subscription::whereBetween('end_date', [$month->toDateString(), $month->copy()->addDays(30)->toDateString()])->count();
        $activeProjects = Project::with(['client', 'stages'])->whereIn('status', ['planning', 'running'])->latest()->take(5)->get();

        return view('dashboard', compact(
            'platformCount',
            'clientCount',
            'packageCount',
            'categoryCount',
            'recentClients',
            'monthlyNet',
            'monthlyFees',
            'balancePending',
            'unpaidInvoices',
            'expiringSubscriptions',
            'activeProjects'
        ));
    }
}
