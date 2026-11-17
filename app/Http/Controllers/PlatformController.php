<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePlatformRequest;
use App\Http\Requests\UpdatePlatformRequest;
use App\Models\Platform;
use Illuminate\Http\Request;

class PlatformController extends Controller
{
    /**
     * Display a listing of platforms.
     */
    public function index()
    {
        $platforms = Platform::withCount('clients')->orderBy('id')->get();
        return view('platforms.index', compact('platforms'));
    }

    /**
     * Show the form for creating a new platform.
     */
    public function create()
    {
        return view('platforms.create');
    }

    /**
     * Store a newly created platform.
     */
    public function store(StorePlatformRequest $request)
    {
        Platform::create($request->validated());

        return redirect()->route('platforms.index')->with('success', 'Platform berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified platform.
     */
    public function edit(Platform $platform)
    {
        return view('platforms.edit', compact('platform'));
    }

    /**
     * Update the specified platform.
     */
    public function update(UpdatePlatformRequest $request, Platform $platform)
    {
        $platform->update($request->validated());

        return redirect()->route('platforms.index')->with('success', 'Data platform berhasil diperbarui.');
    }

    /**
     * Remove the specified platform.
     */
    public function destroy(Platform $platform)
    {
        if ($platform->clients()->exists()) {
            return back()->with('error', 'Platform tidak dapat dihapus karena masih terhubung dengan data klien. Silakan nonaktifkan platform.');
        }

        $platform->delete();

        return redirect()->route('platforms.index')->with('success', 'Platform berhasil dihapus.');
    }

    /**
     * Calculate fee and net amount preview for an amount and platform.
     */
    public function calculate(Request $request, Platform $platform)
    {
        $gross = (int) $request->input('gross_amount', 0);
        $otherFee = (int) $request->input('other_fee', 0);
        $feeOverride = $request->filled('platform_fee_override') ? (int) $request->input('platform_fee_override') : null;

        $platformFee = $feeOverride !== null ? $feeOverride : $platform->calculateFee($gross);
        $net = $platform->calculateNet($gross, $otherFee, $feeOverride);

        return response()->json([
            'gross_amount' => $gross,
            'commission_percent' => (float) $platform->commission_percent,
            'fixed_fee' => $platform->fixed_fee,
            'platform_fee' => $platformFee,
            'other_fee' => $otherFee,
            'net_amount' => $net,
            'formatted_gross' => format_rupiah($gross),
            'formatted_platform_fee' => format_rupiah($platformFee),
            'formatted_other_fee' => format_rupiah($otherFee),
            'formatted_net' => format_rupiah($net),
        ]);
    }
}
