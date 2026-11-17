<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class DocumentTemplate extends Model
{
    protected $fillable=['type','name','body','is_default'];
    protected $casts=['is_default'=>'boolean'];
}
