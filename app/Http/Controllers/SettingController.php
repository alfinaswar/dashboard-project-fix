<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /**
     * Display settings page.
     */
    public function index()
    {
        $settings = [
            'company_name' => Setting::get('company_name', 'Studio Freelance'),
            'company_address' => Setting::get('company_address', ''),
            'company_phone' => Setting::get('company_phone', ''),
            'company_email' => Setting::get('company_email', ''),
            'company_npwp' => Setting::get('company_npwp', ''),
            'bank_name' => Setting::get('bank_name', 'BCA'),
            'bank_account_number' => Setting::get('bank_account_number', ''),
            'bank_account_name' => Setting::get('bank_account_name', ''),
            'signee_name' => Setting::get('signee_name', ''),
            'invoice_due_days' => Setting::get('invoice_due_days', '14'),
            'revision_days' => Setting::get('revision_days', '14'),
            'invoice_number_format' => Setting::get('invoice_number_format', 'INV/{YYYY}/{MM}/{NUMBER}'),
            'agreement_number_format' => Setting::get('agreement_number_format', 'PKS/{YYYY}/{NUMBER}'),
            'app_timezone' => Setting::get('app_timezone', 'Asia/Jakarta'),
            'currency' => Setting::get('currency', 'IDR'),
        ];

        return view('settings.index', compact('settings'));
    }

    /**
     * Update settings.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'company_address' => ['nullable', 'string'],
            'company_phone' => ['nullable', 'string', 'max:50'],
            'company_email' => ['nullable', 'email', 'max:255'],
            'company_npwp' => ['nullable', 'string', 'max:50'],
            'bank_name' => ['required', 'string', 'max:100'],
            'bank_account_number' => ['required', 'string', 'max:100'],
            'bank_account_name' => ['required', 'string', 'max:255'],
            'signee_name' => ['required', 'string', 'max:255'],
            'invoice_due_days' => ['required', 'integer', 'min:1'],
            'revision_days' => ['required', 'integer', 'min:0'],
            'invoice_number_format' => ['required', 'string', 'max:100'],
            'agreement_number_format' => ['required', 'string', 'max:100'],
        ], [
            'company_name.required' => 'Nama usaha wajib diisi.',
            'bank_name.required' => 'Nama bank wajib diisi.',
            'bank_account_number.required' => 'Nomor rekening wajib diisi.',
            'bank_account_name.required' => 'Nama pemilik rekening wajib diisi.',
            'signee_name.required' => 'Nama penandatangan dokumen wajib diisi.',
            'invoice_due_days.required' => 'Default jatuh tempo invoice wajib diisi.',
            'revision_days.required' => 'Default masa revisi wajib diisi.',
            'invoice_number_format.required' => 'Format nomor invoice wajib diisi.',
            'agreement_number_format.required' => 'Format nomor perjanjian wajib diisi.',
        ]);

        foreach ($validated as $key => $value) {
            Setting::set($key, (string) ($value ?? ''));
        }

        return redirect()->route('settings.index')->with('success', 'Pengaturan usaha berhasil disimpan.');
    }
}
