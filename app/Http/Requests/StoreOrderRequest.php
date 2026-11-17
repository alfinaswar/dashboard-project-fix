<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array { return ['client_id'=>['required','exists:clients,id'],'platform_id'=>['required','exists:platforms,id'],'title'=>['required','string','max:255'],'external_order_ref'=>['nullable','string','max:255'],'ordered_at'=>['required','date'],'deadline'=>['nullable','date','after_or_equal:ordered_at'],'status'=>['required','in:draft,in_progress,delivered,completed,cancelled'],'revision_count'=>['required','integer','min:0'],'gross_amount'=>['required','integer','min:0'],'platform_fee_override'=>['nullable','integer','min:0'],'platform_fee_override_reason'=>['nullable','required_with:platform_fee_override','string','max:255'],'other_fee'=>['nullable','integer','min:0'],'other_fee_note'=>['nullable','string','max:255'],'notes'=>['nullable','string'],'fee_items'=>['nullable','array'],'fee_items.*.label'=>['required','string','max:255'],'fee_items.*.amount_type'=>['nullable','in:amount,percent'],'fee_items.*.amount'=>['required','integer','min:0']]; }
    public function messages(): array { return ['client_id.required'=>'Klien wajib dipilih.','platform_id.required'=>'Platform wajib dipilih.','title.required'=>'Judul pekerjaan wajib diisi.','gross_amount.required'=>'Nilai kotor wajib diisi.','platform_fee_override_reason.required_with'=>'Alasan override potongan wajib diisi.','fee_items.*.label.required'=>'Nama potongan wajib diisi.','fee_items.*.amount_type.required'=>'Tipe potongan wajib dipilih.','fee_items.*.amount.required'=>'Nilai potongan wajib diisi.']; }
}