<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Project extends Model
{
    use SoftDeletes;
    protected $fillable=['client_id','name','description','start_date','target_date','status'];
    protected $casts=['start_date'=>'date','target_date'=>'date'];
    public function client(){return $this->belongsTo(Client::class);}
    public function stages(){return $this->hasMany(ProjectStage::class)->orderBy('position');}
    public function getProgressPercentAttribute():float{return round($this->stages->sum(fn($stage)=>(int)$stage->weight*(int)$stage->progress_percent)/100,2);}
}
