<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id', 'name', 'email', 'phone', 'company', 'subject', 'message', 'status',
        'priority', 'assigned_to', 'source',
        'ip_address', 'user_agent', 'device_type', 'browser', 'browser_version', 'os',
        'country', 'country_code', 'region', 'city', 'timezone', 'isp',
        'source_url', 'referrer_url', 'utm_source', 'utm_medium', 'utm_campaign',
        'follow_up_at', 'read_at', 'first_contacted_at', 'last_action_at',
        'erp_customer_id', 'erp_sync_status', 'erp_synced_at', 'erp_synced_by',
    ];

    protected $casts = [
        'follow_up_at' => 'datetime',
        'read_at' => 'datetime',
        'first_contacted_at' => 'datetime',
        'last_action_at' => 'datetime',
        'erp_synced_at' => 'datetime',
    ];

    public function activities()
    {
        return $this->hasMany(MessageActivity::class)->orderByDesc('created_at');
    }

    public function notes()
    {
        return $this->hasMany(MessageNote::class)->orderByDesc('created_at');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to', 'id');
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByPriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeByAssignee($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('email', 'like', "%{$term}%")
              ->orWhere('phone', 'like', "%{$term}%")
              ->orWhere('company', 'like', "%{$term}%")
              ->orWhere('subject', 'like', "%{$term}%")
              ->orWhere('message', 'like', "%{$term}%");
        });
    }

    public function scopeByDateRange($query, ?string $from, ?string $to)
    {
        if ($from) $query->where('created_at', '>=', $from);
        if ($to) $query->where('created_at', '<=', $to . ' 23:59:59');
        return $query;
    }
}
