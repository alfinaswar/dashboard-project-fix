<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class StoreProjectRequest extends FormRequest
{
    public function authorize():bool{return true;}
    public function rules():array{return ['client_id'=>['required','exists:clients,id'],'name'=>['required','string','max:255'],'description'=>['nullable','string'],'start_date'=>['nullable','date'],'target_date'=>['nullable','date','after_or_equal:start_date'],'status'=>['required','in:planning,running,done,on_hold'],'stages'=>['required','array','min:1'],'stages.*.name'=>['required','string','max:255'],'stages.*.weight'=>['required','integer','min:0','max:100'],'stages.*.progress_percent'=>['required','integer','min:0','max:100'],'stages.*.status'=>['required','in:todo,doing,done'],'stages.*.target_date'=>['nullable','date'],'stages.*.actual_date'=>['nullable','date']];}
    public function withValidator($validator):void{$validator->after(function($validator){$weight=collect($this->input('stages',[]))->sum(fn($stage)=>(int)($stage['weight']??0));if($weight!==100)$validator->errors()->add('stages','Total bobot tahapan harus 100.');});}
    public function messages():array{return ['client_id.required'=>'Klien wajib dipilih.','name.required'=>'Nama proyek wajib diisi.','stages.required'=>'Minimal satu tahapan wajib diisi.'];}
}
