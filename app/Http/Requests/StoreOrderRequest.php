<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array { return ['client_id'=>['required','exists:clients,id'],'platform_id'=>['required','exists:platforms,id'],'service_package_id'=>['nullable','exists:service_packages,id'],'title'=>['required','string','max:255'],'external_order_ref'=>['nullable','string','max:255'],'ordered_at'=>['required','date'],'deadline'=>['nullable','date','after_or_equal:ordered_at'],'status'=>['required','in:draft,in_progress,delivered,completed,cancelled'],'revision_count'=>['required','integer','min:0'],'gross_amount'=>['required','integer','min:0'],'platform_fee_override'=>['nullable','integer','min:0'],'platform_fee_override_reason'=>['nullable','required_with:platform_fee_override','string','max:255'],'other_fee'=>['nullable','integer','min:0'],'other_fee_note'=>['nullable','string','max:255'],'notes'=>['nullable','string']]; }
    public function messages(): array { return ['client_id.required'=>'Klien wajib dipilih.','platform_id.required'=>'Platform wajib dipilih.','title.required'=>'Judul pekerjaan wajib diisi.','gross_amount.required'=>'Nilai kotor wajib diisi.','platform_fee_override_reason.required_with'=>'Alasan override potongan wajib diisi.']; }
}
