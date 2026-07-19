<?php

namespace App\Services;

use App\Models\Message;
use App\Models\MessageActivity;
use App\Models\User;

class MessageService
{
    protected NotificationService $notifications;

    public const STATUSES = [
        'new', 'read', 'in_progress', 'waiting_info', 'waiting_customer',
        'quoted', 'meeting_scheduled', 'demo_scheduled', 'follow_up',
        'qualified', 'negotiation', 'won', 'lost',
        'replied', 'spam', 'archived', 'deleted',
    ];

    public const PRIORITIES = ['low', 'normal', 'high', 'urgent'];

    public function __construct(NotificationService $notifications)
    {
        $this->notifications = $notifications;
    }

    public function updateStatus(Message $msg, string $newStatus, User $actor): Message
    {
        $oldStatus = $msg->status;
        if ($oldStatus === $newStatus) return $msg;

        $msg->status = $newStatus;
        $msg->last_action_at = now();

        if ($newStatus === 'read' && !$msg->read_at) {
            $msg->read_at = now();
        }
        if (in_array($newStatus, ['replied', 'quoted']) && !$msg->first_contacted_at) {
            $msg->first_contacted_at = now();
        }

        $msg->save();

        $this->logActivity($msg, $actor, 'status_changed', $oldStatus, $newStatus);
        $this->notifications->notifyStatusChange($msg, $oldStatus, $newStatus, $actor);

        return $msg;
    }

    public function assignTo(Message $msg, ?int $userId, User $actor): Message
    {
        $oldAssignee = $msg->assigned_to;
        $msg->assigned_to = $userId;
        $msg->last_action_at = now();
        $msg->save();

        $assigneeName = $userId ? User::find($userId)?->name ?? 'Unknown' : 'Atanmamış';
        $this->logActivity($msg, $actor, 'assigned', $oldAssignee ? (string)$oldAssignee : null, $assigneeName);

        if ($userId) {
            $assignee = User::find($userId);
            if ($assignee) {
                $this->notifications->notifyAssignment($msg, $assignee, $actor);
            }
        }

        return $msg;
    }

    public function updatePriority(Message $msg, string $priority, User $actor): Message
    {
        $oldPriority = $msg->priority;
        if ($oldPriority === $priority) return $msg;

        $msg->priority = $priority;
        $msg->last_action_at = now();
        $msg->save();

        $this->logActivity($msg, $actor, 'priority_changed', $oldPriority, $priority);

        return $msg;
    }

    public function setFollowUp(Message $msg, ?string $date, User $actor): Message
    {
        $msg->follow_up_at = $date;
        $msg->last_action_at = now();
        $msg->save();

        $this->logActivity($msg, $actor, 'follow_up_set', null, $date);

        return $msg;
    }

    public function bulkUpdateStatus(array $ids, string $status, User $actor): int
    {
        $messages = Message::whereIn('id', $ids)->get();
        foreach ($messages as $msg) {
            $this->updateStatus($msg, $status, $actor);
        }
        return $messages->count();
    }

    public function bulkAssign(array $ids, int $userId, User $actor): int
    {
        $messages = Message::whereIn('id', $ids)->get();
        foreach ($messages as $msg) {
            $this->assignTo($msg, $userId, $actor);
        }
        return $messages->count();
    }

    public function bulkDelete(array $ids, User $actor): int
    {
        $messages = Message::whereIn('id', $ids)->get();
        foreach ($messages as $msg) {
            $this->updateStatus($msg, 'deleted', $actor);
        }
        return $messages->count();
    }

    public function logActivity(Message $msg, User $actor, string $action, ?string $oldValue = null, ?string $newValue = null, ?string $details = null): MessageActivity
    {
        return MessageActivity::create([
            'message_id' => $msg->id,
            'user_id' => $actor->id,
            'user_name' => $actor->name,
            'action' => $action,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'details' => $details,
        ]);
    }
}
