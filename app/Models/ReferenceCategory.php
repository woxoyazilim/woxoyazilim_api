<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ReferenceCategory extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = ['id','name','slug','icon','sort_order','is_active'];
    protected $casts = ['is_active' => 'boolean'];
}
