<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class UploadSignedAgreementRequest extends FormRequest
{
    public function authorize():bool{return true;} public function rules():array{return ['signed_file'=>['required','file','mimes:pdf,doc,docx,jpg,jpeg,png','max:5120'],'signed_at'=>['required','date']];} public function messages():array{return ['signed_file.required'=>'File perjanjian bertanda tangan wajib diunggah.','signed_file.max'=>'Ukuran file maksimal 5 MB.'];}
}
