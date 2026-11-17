<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array { return ['source'=>['required','in:generated,uploaded'],'order_id'=>['nullable','exists:orders,id'],'number'=>['nullable','string','max:255','unique:invoices,number'],'template_id'=>['nullable','exists:document_templates,id'],'issued_at'=>['required','date'],'due_date'=>['required','date','after_or_equal:issued_at'],'subtotal'=>['required','integer','min:0'],'tax_amount'=>['required','integer','min:0'],'status'=>['required','in:draft,sent,unpaid'],'file'=>['required_if:source,uploaded','file','mimes:pdf,jpg,jpeg,png','max:5120']]; }
    public function messages(): array { return ['order_id.required'=>'Order wajib dipilih.','file.required_if'=>'File invoice wajib diunggah.','file.max'=>'Ukuran file maksimal 5 MB.','file.mimes'=>'File harus berupa PDF atau gambar.']; }
}
