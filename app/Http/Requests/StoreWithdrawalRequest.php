<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class StoreWithdrawalRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array { return ['platform_id'=>['required','exists:platforms,id'],'withdrawn_at'=>['required','date'],'amount_received'=>['required','integer','min:0'],'fee'=>['required','integer','min:0'],'bank_name'=>['required','string','max:255'],'reference_no'=>['nullable','string','max:255'],'notes'=>['nullable','string'],'order_ids'=>['required','array','min:1'],'order_ids.*'=>['integer','exists:orders,id']]; }
    public function messages(): array { return ['order_ids.required'=>'Pilih minimal satu order.','bank_name.required'=>'Nama bank wajib diisi.']; }
}
