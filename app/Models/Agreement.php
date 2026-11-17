<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Agreement extends Model
{
    protected $fillable=['client_id','order_id','template_id','number','title','status','current_version','signed_file_path','signed_at'];
    protected $casts=['signed_at'=>'date'];
    public function client(){return $this->belongsTo(Client::class);} public function order(){return $this->belongsTo(Order::class);} public function template(){return $this->belongsTo(DocumentTemplate::class);} public function versions(){return $this->hasMany(AgreementVersion::class);}
}
