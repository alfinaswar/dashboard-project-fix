<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class StoreAgreementRequest extends FormRequest
{
    public function authorize(): bool{return true;} public function rules():array{return ['client_id'=>['required','exists:clients,id'],'order_id'=>['nullable','exists:orders,id'],'template_id'=>['required','exists:document_templates,id'],'title'=>['required','string','max:255'],'variables'=>['nullable','array'],'variables.*'=>['nullable','string']];} public function messages():array{return ['client_id.required'=>'Klien wajib dipilih.','template_id.required'=>'Template wajib dipilih.','title.required'=>'Judul perjanjian wajib diisi.'];}
}
