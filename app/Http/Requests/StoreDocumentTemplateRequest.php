<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class StoreDocumentTemplateRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array { return ['type'=>['required','in:invoice,agreement'],'name'=>['required','string','max:255'],'body'=>['required','string'],'is_default'=>['nullable','boolean']]; }
    public function messages(): array { return ['name.required'=>'Nama template wajib diisi.','body.required'=>'Isi template wajib diisi.']; }
}
