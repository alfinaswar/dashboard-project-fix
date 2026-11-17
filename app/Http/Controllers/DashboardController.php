<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ExpenseCategory;
use App\Models\Platform;
use App\Models\ServicePackage;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display dashboard.
     */
    public function index()
    {
        $platformCount = Platform::count();
        $clientCount = Client::count();
        $packageCount = ServicePackage::count();
        $categoryCount = ExpenseCategory::count();

        $recentClients = Client::with('platform')->latest()->take(5)->get();

        return view('dashboard', compact(
            'platformCount',
            'clientCount',
            'packageCount',
            'categoryCount',
            'recentClients'
        ));
    }
}
