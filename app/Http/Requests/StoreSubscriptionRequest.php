<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubscriptionRequest extends FormRequest
{
    public function authorize():bool{return true;} public function rules():array{return ['client_id'=>['required','exists:clients,id'],'billing_type'=>['required','in:one_time,annual,monthly'],'start_date'=>['required','date'],'end_date'=>['nullable','date','after_or_equal:start_date'],'contract_value'=>['required','integer','min:0'],'status'=>['required','in:active,expiring,expired,frozen,renewed'],'freeze_reason'=>['nullable','required_if:status,frozen','string','max:255'],'notes'=>['nullable','string']];} public function messages():array{return ['client_id.required'=>'Klien wajib dipilih.','billing_type.required'=>'Jenis langganan wajib dipilih.','freeze_reason.required_if'=>'Alasan pembekuan wajib diisi.'];}}
