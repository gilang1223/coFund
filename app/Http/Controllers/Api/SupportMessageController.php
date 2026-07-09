<?php

namespace App\Http\Controllers\Api;

use App\Models\SupportMessage;
use App\Models\User;
use App\Jobs\SendNotificationJob;
use Illuminate\Http\Request;

class SupportMessageController extends ApiController
{
    /**
     * Get all messages for the authenticated user.
     */
    public function index()
    {
        $messages = SupportMessage::forUser(auth()->id())
            ->orderBy('created_at', 'asc')
            ->get();

        // Mark all admin messages as read
        SupportMessage::forUser(auth()->id())
            ->where('is_from_admin', true)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return $this->sendResponse('Messages retrieved', $messages);
    }

    /**
     * Send a message as the authenticated user.
     */
    public function store(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        $message = SupportMessage::create([
            'user_id' => auth()->id(),
            'message' => $request->message,
            'is_from_admin' => false,
        ]);

        // Notify all admins
        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            SendNotificationJob::dispatch(
                $admin->id,
                'system',
                'Pesan Baru dari User 💬',
                'Ada pesan support baru dari ' . auth()->user()->name . ': "' . \Illuminate\Support\Str::limit($request->message, 50) . '"',
                ['user_id' => auth()->id(), 'message_id' => $message->id],
                false // don't send email
            );
        }

        return $this->sendCreated('Pesan terkirim', $message);
    }

    /**
     * Admin: get all conversations grouped by user.
     */
    public function adminConversations()
    {
        $users = SupportMessage::fromUsers()
            ->with('user')
            ->select('user_id')
            ->distinct()
            ->get()
            ->pluck('user_id');

        $conversations = [];
        foreach ($users as $userId) {
            $lastMessage = SupportMessage::forUser($userId)
                ->orderBy('created_at', 'desc')
                ->first();

            $unreadCount = SupportMessage::forUser($userId)
                ->where('is_from_admin', false)
                ->where('is_read', false)
                ->count();

            $conversations[] = [
                'user' => $lastMessage?->user,
                'user_id' => $userId,
                'last_message' => $lastMessage?->message,
                'last_message_at' => $lastMessage?->created_at,
                'unread_count' => $unreadCount,
            ];
        }

        // Sort by latest message
        usort($conversations, fn($a, $b) => strtotime($b['last_message_at'] ?? '0000-00-00') - strtotime($a['last_message_at'] ?? '0000-00-00'));

        return $this->sendResponse('Conversations retrieved', $conversations);
    }

    /**
     * Admin: get messages for a specific user conversation.
     */
    public function adminConversation(int $userId)
    {
        $messages = SupportMessage::forUser($userId)
            ->orderBy('created_at', 'asc')
            ->get();

        // Mark user messages as read
        SupportMessage::forUser($userId)
            ->where('is_from_admin', false)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return $this->sendResponse('Conversation messages retrieved', $messages);
    }

    /**
     * Admin: reply to a user conversation.
     */
    public function adminReply(Request $request, int $userId)
    {
        $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        $message = SupportMessage::create([
            'user_id' => $userId,
            'admin_id' => auth()->id(),
            'message' => $request->message,
            'is_from_admin' => true,
        ]);

        // Notify the user
        SendNotificationJob::dispatch(
            $userId,
            'system',
            'Balasan dari Admin 💬',
            'Admin membalas pesan support Anda: "' . \Illuminate\Support\Str::limit($request->message, 50) . '"',
            ['admin_id' => auth()->id(), 'message_id' => $message->id],
            false // don't send email
        );

        return $this->sendCreated('Balasan terkirim', $message);
    }
}
