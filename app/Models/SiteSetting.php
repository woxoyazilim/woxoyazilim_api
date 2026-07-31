<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $table = 'site_settings';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['id', 'value'];

    protected $casts = [
        'value' => 'array',
    ];
}
