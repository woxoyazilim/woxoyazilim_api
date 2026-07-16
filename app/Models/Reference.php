<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Reference extends Model
{
    protected $table = 'references_portfolio';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id','title','description','image','screenshot_url','screenshot_status',
        'screenshot_error','screenshot_updated_at','url','category','subcategory',
        'services','year','industry','type','featured','sort_order','screenshot_requested_at'
    ];

    protected $casts = [
        'services' => 'array',
        'featured' => 'boolean',
        'screenshot_updated_at' => 'datetime',
        'screenshot_requested_at' => 'datetime',
    ];
}
