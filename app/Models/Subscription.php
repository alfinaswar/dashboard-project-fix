<?php
namespace App\Models;
use Carbon\Carbon; use Illuminate\Database\Eloquent\Model; use Illuminate\Database\Eloquent\SoftDeletes;
class Subscription extends Model
{
    use SoftDeletes; protected $fillable=['client_id','service_package_id','previous_subscription_id','start_date','end_date','contract_value','status','freeze_reason','notes']; protected $casts=['start_date'=>'date','end_date'=>'date','contract_value'=>'integer','reminder_30_sent_at'=>'datetime','reminder_14_sent_at'=>'datetime','reminder_7_sent_at'=>'datetime','expired_notified_at'=>'datetime'];
    public function client(){return $this->belongsTo(Client::class);} public function servicePackage(){return $this->belongsTo(ServicePackage::class);} public function previous(){return $this->belongsTo(self::class,'previous_subscription_id');} public function next(){return $this->hasOne(self::class,'previous_subscription_id');}
    public function getCalculatedStatusAttribute():string{if($this->status==='frozen'||$this->status==='renewed')return $this->status;if($this->end_date->isPast())return 'expired';return now()->diffInDays($this->end_date,false)<=30?'expiring':'active';}
    public function renewalStartDate(){return $this->end_date->copy()->addDay();}
}
