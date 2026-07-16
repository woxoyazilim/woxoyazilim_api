<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Subcategory extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;
    protected $fillable = ['id','name','sector_id'];
}
