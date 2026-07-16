<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class DemoRequest extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = ['id','name','email','phone','company','software_name','software_slug','employee_count','service_id','message','status'];
}
