<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class AgreementVersion extends Model
{
    public $timestamps=false; protected $fillable=['agreement_id','version_number','variables','body_rendered','pdf_path','docx_path']; protected $casts=['variables'=>'array','created_at'=>'datetime']; public function agreement(){return $this->belongsTo(Agreement::class);}
}
