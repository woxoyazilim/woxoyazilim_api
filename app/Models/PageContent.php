<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class PageContent extends Model
{
    protected $table = 'page_content';
    protected $primaryKey = 'slug';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;
    protected $fillable = ['slug','content'];
    protected $casts = ['content'=>'array'];
}
