<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessageNote extends Model
{
    protected $fillable = [
        'message_id', 'user_id', 'user_name',
        'content', 'type', 'is_internal', 'is_pinned',
    ];

    protected $casts = [
        'is_internal' => 'boolean',
        'is_pinned' => 'boolean',
    ];

    public function message()
    {
        return $this->belongsTo(Message::class);
    }
}
