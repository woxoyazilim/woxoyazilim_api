<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = ['id','title','slug','short_description','description','content','icon','image','category','technologies','features','sort_order','is_active','seo_title','seo_description','seo_keywords'];
    protected $casts = ['content'=>'array','technologies'=>'array','features'=>'array','is_active'=>'boolean'];
}
