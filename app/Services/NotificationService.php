<?php

namespace App\Services;

use App\Models\UserNotification;

class NotificationService extends BaseService
{
    /**
     * Get notifications for a user.
     */
    public function getByUser(int $userId)
    {
        return UserNotification::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get unread notifications for a user.
     */
    public function getUnread(int $userId)
    {
        return UserNotification::where('user_id', $userId)
            ->whereNull('read_at')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Create a notification.
     */
    public function create(array $data): UserNotification
    {
        return UserNotification::create([
            'user_id' => $data['user_id'],
            'type' => $data['type'],
            'title' => $data['title'],
            'body' => $data['body'],
            'data' => $data['data'] ?? null,
        ]);
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead(int $id): ?UserNotification
    {
        $notification = UserNotification::find($id);

        if (!$notification) {
            return null;
        }

        $notification->update(['read_at' => now()]);

        return $notification->fresh();
    }

    /**
     * Mark all notifications as read for a user.
     */
    public function markAllAsRead(int $userId): bool
    {
        return UserNotification::where('user_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]) > 0;
    }

    /**
     * Delete a notification.
     */
    public function delete(int $id): bool
    {
        $notification = UserNotification::find($id);

        if (!$notification) {
            return false;
        }

        return $notification->delete();
    }
}
