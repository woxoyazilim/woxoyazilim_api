<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessageActivity extends Model
{
    protected $fillable = [
        'message_id', 'user_id', 'user_name',
        'action', 'old_value', 'new_value', 'details',
    ];

    public function message()
    {
        return $this->belongsTo(Message::class);
    }
}
