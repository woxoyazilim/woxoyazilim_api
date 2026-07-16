<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ChatLead extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = ['id','name','email','phone','messages','source'];
    protected $casts = ['messages'=>'array'];
}
