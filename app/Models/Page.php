<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = ['id','slug','title','content'];
    protected $casts = ['content'=>'array'];
}
