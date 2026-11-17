<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ActivityLog extends Model
{
    public $timestamps=false;
    protected $fillable=['user_id','subject_type','subject_id','action','changes','created_at'];
    protected $casts=['changes'=>'array','created_at'=>'datetime'];
    public function subject(){return $this->morphTo();}
    public function user(){return $this->belongsTo(User::class);}
}
