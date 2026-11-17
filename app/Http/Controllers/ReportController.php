<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->date('from')?->startOfDay() ?? now()->startOfMonth();
        $to = $request->date('to')?->endOfDay() ?? now()->endOfMonth();
        $orders = Order::with('platform')->whereBetween('ordered_at', [$from, $to])->latest('ordered_at')->paginate(25)->withQueryString();
        return view('reports.index', compact('orders', 'from', 'to'));
    }

    public function export(Request $request)
    {
        $from = $request->date('from')?->startOfDay() ?? now()->startOfMonth();
        $to = $request->date('to')?->endOfDay() ?? now()->endOfMonth();
        $orders = Order::with('platform')->whereBetween('ordered_at', [$from, $to])->orderBy('ordered_at')->get();
        return response()->streamDownload(function () use ($orders) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Tanggal', 'Judul', 'Platform', 'Status', 'Kotor', 'Potongan', 'Bersih']);
            foreach ($orders as $order) fputcsv($out, [$order->ordered_at?->format('Y-m-d'), $order->title, $order->platform?->name ?? 'Langsung', $order->status, $order->gross_amount, $order->platform_fee, $order->net_amount]);
            fclose($out);
        }, 'laporan-order.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
