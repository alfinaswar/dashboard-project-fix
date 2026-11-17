<?php

namespace App\Http\Controllers;

use App\Models\ServicePackage;
use Illuminate\Http\Request;

class ServicePackageController extends Controller
{
    public function index()
    {
        $packages = ServicePackage::latest()->paginate(15);
        return view('service-packages.index', compact('packages'));
    }

    public function create()
    {
        return view('service-packages.form', ['package' => new ServicePackage()]);
    }

    public function store(Request $request)
    {
        ServicePackage::create($this->validated($request));
        return redirect()->route('service-packages.index')->with('success', 'Paket layanan berhasil ditambahkan.');
    }

    public function edit(ServicePackage $service_package)
    {
        return view('service-packages.form', ['package' => $service_package]);
    }

    public function update(Request $request, ServicePackage $service_package)
    {
        $service_package->update($this->validated($request));
        return redirect()->route('service-packages.index')->with('success', 'Paket layanan berhasil diperbarui.');
    }

    public function destroy(ServicePackage $service_package)
    {
        $service_package->delete();
        return redirect()->route('service-packages.index')->with('success', 'Paket layanan berhasil dihapus.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'default_price' => ['required', 'integer', 'min:0'],
            'default_duration_months' => ['required', 'integer', 'min:1'],
            'billing_type' => ['required', 'in:one_time,subscription'],
            'is_active' => ['nullable', 'boolean'],
        ], ['name.required' => 'Nama paket wajib diisi.', 'billing_type.required' => 'Jenis paket wajib dipilih.']);
    }
}
