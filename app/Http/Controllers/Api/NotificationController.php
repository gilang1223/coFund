<?php

namespace App\Http\Controllers\Api;

use App\Models\UserNotification;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class NotificationController extends Controller
{
    use ApiResponse;

    /**
     * Get all notifications for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 20);
        $type = $request->get('type');

        $query = UserNotification::where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc');

        if ($type) {
            $query->where('type', $type);
        }

        $notifications = $query->paginate($perPage);

        return $this->success([
            'notifications' => $notifications,
            'unread_count' => UserNotification::where('user_id', $request->user()->id)
                ->whereNull('read_at')
                ->count(),
        ]);
    }

    /**
     * Get unread notification count.
     */
    public function unreadCount(Request $request): JsonResponse
    {
        $count = UserNotification::where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->count();

        return $this->success(['unread_count' => $count]);
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead(Request $request, int $id): JsonResponse
    {
        $notification = UserNotification::where('user_id', $request->user()->id)
            ->findOrFail($id);

        $notification->update(['read_at' => now()]);

        return $this->success(['notification' => $notification]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        UserNotification::where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return $this->success(['message' => 'All notifications marked as read']);
    }

    /**
     * Delete a notification.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $notification = UserNotification::where('user_id', $request->user()->id)
            ->findOrFail($id);

        $notification->delete();

        return $this->success(['message' => 'Notification deleted']);
    }
}
