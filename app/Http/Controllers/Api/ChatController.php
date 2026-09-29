<?php

namespace App\Http\Controllers\Api;

use App\Events\ConversationCreated;
use App\Events\MessageDeleted;
use App\Events\MessageRead;
use App\Events\MessageSent;
use App\Events\UnreadCountUpdated;
use App\Events\UserTyping;
use App\Http\Controllers\Controller;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\User;
use App\Notifications\NewChatMessageForAdmin;
use App\Notifications\NewChatMessageForUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | USER: Get Chat Messages
    |--------------------------------------------------------------------------
    */

    public function userMessages(Request $request)
    {
        $user = Auth::user();

        $conversation = ChatConversation::firstOrCreate(
            ['user_id' => $user->id],
            ['unread_admin' => 0, 'unread_user' => 0]
        );

        $messages = $conversation->messages()
            ->orderBy('id', 'asc')
            ->get();

        $conversation->update(['unread_user' => 0]);

        $conversation->messages()
            ->where('sender_type', 'admin')
            ->whereNull('read_at')
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        broadcast(new UnreadCountUpdated((int) $user->id, 0));

        return response()->json([
            'success' => true,
            'conversation_id' => $conversation->id,
            'messages' => $messages,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | USER: Send Message
    |--------------------------------------------------------------------------
    */

    public function userSendMessage(Request $request)
    {
        $request->validate([
            'message' => ['nullable', 'string', 'max:5000'],
            'attachment' => ['nullable', 'file', 'max:20480'],
        ]);

        if (!$request->filled('message') && !$request->hasFile('attachment')) {
            return response()->json([
                'success' => false,
                'message' => 'Please enter a message or attach a file.',
            ], 422);
        }

        $user = Auth::user();

        $conversation = ChatConversation::firstOrCreate(
            ['user_id' => $user->id],
            ['unread_admin' => 0, 'unread_user' => 0]
        );

        $isNewConversation = $conversation->messages()->count() === 0;

        $messageText = trim((string) $request->input('message', ''));

        $attachmentData = $this->storeAttachment($request);

        $message = ChatMessage::create([
            'conversation_id' => $conversation->id,
            'sender_type' => 'user',
            'sender_id' => $user->id,
            'message' => $messageText ?: null,
            'is_read' => false,
            ...$attachmentData,
        ]);

        $conversation->update([
            'last_message' => $messageText ?: '📎 Attachment',
            'unread_admin' => $conversation->unread_admin + 1,
        ]);

        broadcast(new MessageSent($message));

        if ($isNewConversation) {
            broadcast(new ConversationCreated($conversation->fresh('user')));
        }

        $admins = User::where('role', 'admin')
            ->where('is_active', true)
            ->get();

        foreach ($admins as $admin) {
            $admin->notify(
                new NewChatMessageForAdmin($conversation, $user->name)
            );
        }

        return response()->json([
            'success' => true,
            'message' => $message->fresh(),
        ], 201);
    }


    /*
    |--------------------------------------------------------------------------
    | USER: Unread Count
    |--------------------------------------------------------------------------
    */

    public function userUnreadCount()
    {
        $user = Auth::user();

        $conversation = ChatConversation::where('user_id', $user->id)->first();

        return response()->json([
            'success' => true,
            'count' => $conversation?->unread_user ?? 0,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | USER: Mark messages as read
    |--------------------------------------------------------------------------
    */

    public function userMarkAsRead(Request $request)
    {
        $user = Auth::user();

        $conversation = ChatConversation::where('user_id', $user->id)->first();

        if (!$conversation) {
            return response()->json(['success' => false], 404);
        }

        $now = now();
        $marked = 0;

        $messages = $conversation->messages()
            ->where('sender_type', 'admin')
            ->whereNull('read_at')
            ->get();

        foreach ($messages as $message) {
            $message->update(['read_at' => $now, 'is_read' => true]);
            broadcast(new MessageRead($message));
            $marked++;
        }

        return response()->json([
            'success' => true,
            'marked' => $marked,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | USER: Broadcast typing
    |--------------------------------------------------------------------------
    */

    public function userTyping(Request $request)
    {
        $user = Auth::user();

        broadcast(new UserTyping(
            (int) $user->id,
            'user',
            $user->name
        ))->toOthers();

        return response()->json(['success' => true]);
    }


    /*
    |--------------------------------------------------------------------------
    | ADMIN: Get All Conversations
    |--------------------------------------------------------------------------
    */

    public function adminConversations()
    {
        $conversations = ChatConversation::with('user')
            ->withCount([
                'messages as unread_messages_count' => function ($query) {
                    $query->where('sender_type', 'user')
                        ->where('is_read', false);
                },
            ])
            ->orderByDesc('updated_at')
            ->get();

        return response()->json([
            'success' => true,
            'chats' => $conversations,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | ADMIN: Get Specific User Conversation
    |--------------------------------------------------------------------------
    */

    public function adminMessages($userId)
    {
        $user = User::find($userId);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 404);
        }

        $conversation = ChatConversation::where('user_id', $user->id)->first();

        if (!$conversation) {
            return response()->json([
                'success' => true,
                'conversation_id' => null,
                'user' => $user,
                'messages' => [],
            ]);
        }

        $messages = $conversation->messages()
            ->orderBy('id', 'asc')
            ->get();

        $conversation->update(['unread_admin' => 0]);

        $conversation->messages()
            ->where('sender_type', 'user')
            ->whereNull('read_at')
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'conversation_id' => $conversation->id,
            'user' => $user,
            'messages' => $messages,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | ADMIN: Send Message
    |--------------------------------------------------------------------------
    */

    public function adminSendMessage(Request $request, $userId)
    {
        $request->validate([
            'message' => ['nullable', 'string', 'max:5000'],
            'attachment' => ['nullable', 'file', 'max:20480'],
        ]);

        if (!$request->filled('message') && !$request->hasFile('attachment')) {
            return response()->json([
                'success' => false,
                'message' => 'Please enter a message or attach a file.',
            ], 422);
        }

        $user = User::find($userId);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 404);
        }

        $conversation = ChatConversation::firstOrCreate(
            ['user_id' => $user->id],
            ['unread_admin' => 0, 'unread_user' => 0]
        );

        $messageText = trim((string) $request->input('message', ''));

        $attachmentData = $this->storeAttachment($request);

        $message = ChatMessage::create([
            'conversation_id' => $conversation->id,
            'sender_type' => 'admin',
            'sender_id' => Auth::id(),
            'message' => $messageText ?: null,
            'is_read' => false,
            ...$attachmentData,
        ]);

        $conversation->update([
            'last_message' => $messageText ?: '📎 Attachment',
            'unread_user' => $conversation->unread_user + 1,
        ]);

        broadcast(new MessageSent($message));

        broadcast(new UnreadCountUpdated(
            (int) $user->id,
            (int) $conversation->fresh()->unread_user
        ));

        $user->notify(new NewChatMessageForUser($conversation));

        return response()->json([
            'success' => true,
            'message' => $message->fresh(),
        ], 201);
    }


    /*
    |--------------------------------------------------------------------------
    | ADMIN: Unread Chats Count
    |--------------------------------------------------------------------------
    */

    public function adminUnreadCount()
    {
        $count = ChatConversation::where('unread_admin', '>', 0)
            ->sum('unread_admin');

        return response()->json([
            'success' => true,
            'count' => $count,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | ADMIN: Mark messages as read
    |--------------------------------------------------------------------------
    */

    public function adminMarkAsRead($userId)
    {
        $conversation = ChatConversation::where('user_id', $userId)->first();

        if (!$conversation) {
            return response()->json(['success' => false], 404);
        }

        $now = now();
        $marked = 0;

        $messages = $conversation->messages()
            ->where('sender_type', 'user')
            ->whereNull('read_at')
            ->get();

        foreach ($messages as $message) {
            $message->update(['read_at' => $now, 'is_read' => true]);
            broadcast(new MessageRead($message));
            $marked++;
        }

        return response()->json([
            'success' => true,
            'marked' => $marked,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | ADMIN: Broadcast typing
    |--------------------------------------------------------------------------
    */

    public function adminTyping(Request $request, $userId)
    {
        $admin = Auth::user();

        broadcast(new UserTyping(
            (int) $userId,
            'admin',
            $admin->name
        ))->toOthers();

        return response()->json(['success' => true]);
    }


    /*
    |--------------------------------------------------------------------------
    | DELETE MESSAGE
    |--------------------------------------------------------------------------
    */

    public function deleteMessage($messageId)
    {
        $user = Auth::user();

        $message = ChatMessage::find($messageId);

        if (!$message) {
            return response()->json([
                'success' => false,
                'message' => 'Message not found.',
            ], 404);
        }

        if ($user->role !== 'admin') {
            if (
                $message->sender_type !== 'user' ||
                (int) $message->sender_id !== (int) $user->id
            ) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized.',
                ], 403);
            }
        }

        $message->delete();

        broadcast(new MessageDeleted($message));

        return response()->json([
            'success' => true,
            'message' => 'Message deleted.',
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | HELPER: Store Attachment
    |--------------------------------------------------------------------------
    */

    private function storeAttachment(Request $request): array
    {
        if (!$request->hasFile('attachment')) {
            return [
                'attachment_path' => null,
                'attachment_name' => null,
                'attachment_type' => null,
                'attachment_size' => null,
            ];
        }

        $file = $request->file('attachment');

        $path = $file->store('chat-attachments', 'public');

        return [
            'attachment_path' => $path,
            'attachment_name' => $file->getClientOriginalName(),
            'attachment_type' => $file->getClientMimeType(),
            'attachment_size' => $file->getSize(),
        ];
    }
}