<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ReferenceService extends Model
{
    public $timestamps = false;
    protected $fillable = ['reference_id','service_id','service_name'];
}
