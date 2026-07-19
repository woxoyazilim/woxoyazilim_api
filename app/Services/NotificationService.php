<?php

namespace App\Services;

use App\Models\AdminNotification;
use App\Models\Message;
use App\Models\User;

class NotificationService
{
    public function create(int $userId, string $type, string $title, ?string $body = null, ?string $link = null, ?string $relatedId = null, ?string $relatedType = null): AdminNotification
    {
        return AdminNotification::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'link' => $link,
            'related_id' => $relatedId,
            'related_type' => $relatedType,
        ]);
    }

    public function notifyNewMessage(Message $msg): void
    {
        $admins = User::whereIn('role', ['super_admin', 'admin'])->where('is_active', true)->get();
        foreach ($admins as $admin) {
            $this->create(
                $admin->id,
                'new_message',
                'Yeni talep: ' . $msg->name,
                mb_substr($msg->message, 0, 100),
                '/admin/messages/' . $msg->id,
                $msg->id,
                'message'
            );
        }
    }

    public function notifyAssignment(Message $msg, User $assignee, User $actor): void
    {
        if ($assignee->id == $actor->id) return;

        $this->create(
            $assignee->id,
            'assignment',
            'Size yeni talep atandı: ' . $msg->name,
            $msg->subject ?: mb_substr($msg->message, 0, 100),
            '/admin/messages/' . $msg->id,
            $msg->id,
            'message'
        );
    }

    public function notifyStatusChange(Message $msg, string $oldStatus, string $newStatus, User $actor): void
    {
        if (!$msg->assigned_to || $msg->assigned_to == $actor->id) return;

        $this->create(
            $msg->assigned_to,
            'status_change',
            "{$msg->name} talebi güncellendi",
            "{$oldStatus} -> {$newStatus}",
            '/admin/messages/' . $msg->id,
            $msg->id,
            'message'
        );
    }
}
