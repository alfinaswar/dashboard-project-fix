<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_id' => ['required', 'exists:orders,id'],
            'client_id' => ['nullable', 'exists:clients,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'start_date' => ['nullable', 'date'],
            'target_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['required', 'in:planning,running,done,on_hold'],
            'stages' => ['required', 'array', 'min:1'],
            'stages.*.name' => ['required', 'string', 'max:255'],
            'stages.*.weight' => ['required', 'integer', 'min:0', 'max:100'],
            'stages.*.progress_percent' => ['required', 'integer', 'min:0', 'max:100'],
            'stages.*.status' => ['required', 'in:todo,doing,review,done'],
            'stages.*.target_date' => ['nullable', 'date'],
            'stages.*.actual_date' => ['nullable', 'date'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $stages = collect($this->input('stages', []));
            $weight = $stages->sum(fn ($stage) => (int) ($stage['weight'] ?? 0));
            if ($weight !== 100 && $stages->isNotEmpty()) {
                $validator->errors()->add('stages', 'Total bobot semua tahapan harus bernilai 100% (saat ini: ' . $weight . '%).');
            }
        });
    }

    public function messages(): array
    {
        return [
            'order_id.required' => 'Order pekerjaan wajib dipilih.',
            'order_id.exists' => 'Order yang dipilih tidak ditemukan di sistem.',
            'name.required' => 'Nama proyek wajib diisi.',
            'stages.required' => 'Minimal satu tahapan progress proyek wajib diisi.',
            'target_date.after_or_equal' => 'Target selesai harus sama dengan atau setelah tanggal mulai.',
        ];
    }
}
