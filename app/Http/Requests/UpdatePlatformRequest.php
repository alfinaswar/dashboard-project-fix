<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePlatformRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $platformId = $this->route('platform')?->id ?? $this->route('platform');

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('platforms', 'code')->ignore($platformId)],
            'commission_percent' => ['required', 'numeric', 'between:0,100'],
            'fixed_fee' => ['required', 'integer', 'min:0'],
            'withdrawal_fee' => ['required', 'integer', 'min:0'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama platform wajib diisi.',
            'code.required' => 'Kode unik platform wajib diisi.',
            'code.unique' => 'Kode platform sudah digunakan.',
            'code.alpha_dash' => 'Kode platform hanya boleh berisi huruf, angka, tanda hubung, atau garis bawah.',
            'commission_percent.required' => 'Persentase komisi wajib diisi.',
            'commission_percent.numeric' => 'Persentase komisi harus berupa angka.',
            'commission_percent.between' => 'Persentase komisi harus bernilai antara 0 hingga 100.',
            'fixed_fee.required' => 'Biaya tetap platform wajib diisi (isi 0 jika tidak ada).',
            'fixed_fee.integer' => 'Biaya tetap harus berupa bilangan bulat.',
            'withdrawal_fee.required' => 'Biaya penarikan wajib diisi (isi 0 jika tidak ada).',
            'withdrawal_fee.integer' => 'Biaya penarikan harus berupa bilangan bulat.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
        ]);
    }
}
