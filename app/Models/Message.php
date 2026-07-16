<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = ['id','name','email','phone','company','subject','message','status'];
}
