<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class LandingPage extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = ['id','region','service_title','slug','h1','meta_title','meta_description','content','hero_subtitle','cta_text','cta_url','faq','is_active','sort_order'];
    protected $casts = ['content'=>'array','faq'=>'array','is_active'=>'boolean'];
}
