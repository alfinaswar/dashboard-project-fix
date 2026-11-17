<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array { return ['invoice_id'=>['required','exists:invoices,id'],'paid_at'=>['required','date'],'amount'=>['required','integer','min:1'],'method'=>['required','in:transfer,cash,other'],'reference_no'=>['nullable','string','max:255']]; }
    public function messages(): array { return ['invoice_id.required'=>'Invoice wajib dipilih.','amount.min'=>'Jumlah pembayaran minimal Rp 1.']; }
}
