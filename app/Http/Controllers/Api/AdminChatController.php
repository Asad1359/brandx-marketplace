<?php

namespace App\Http\Controllers\Api;

use App\Events\ConversationCreated;
use App\Events\MessageSent;
use App\Events\UnreadCountUpdated;
use App\Http\Controllers\Controller;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminChatController extends Controller
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
            ->where('is_read', false)
            ->update(['is_read' => true]);

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
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $user = Auth::user();

        $conversation = ChatConversation::firstOrCreate(
            ['user_id' => $user->id],
            ['unread_admin' => 0, 'unread_user' => 0]
        );

        $isNewConversation = $conversation->messages()->count() === 0;

        $messageText = trim($request->message);

        $message = ChatMessage::create([
            'conversation_id' => $conversation->id,
            'sender_type' => 'user',
            'sender_id' => $user->id,
            'message' => $messageText,
            'is_read' => false,
        ]);

        $conversation->update([
            'last_message' => $messageText,
            'unread_admin' => $conversation->unread_admin + 1,
        ]);

        broadcast(new MessageSent($message));

        if ($isNewConversation) {
            broadcast(new ConversationCreated($conversation->fresh('user')));
        }

        return response()->json([
            'success' => true,
            'message' => $message,
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
            ->where('is_read', false)
            ->update(['is_read' => true]);

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
            'message' => ['required', 'string', 'max:5000'],
        ]);

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

        $messageText = trim($request->message);

        $message = ChatMessage::create([
            'conversation_id' => $conversation->id,
            'sender_type' => 'admin',
            'sender_id' => Auth::id(),
            'message' => $messageText,
            'is_read' => false,
        ]);

        $conversation->update([
            'last_message' => $messageText,
            'unread_user' => $conversation->unread_user + 1,
        ]);

        broadcast(new MessageSent($message));

        broadcast(new UnreadCountUpdated(
            (int) $user->id,
            (int) $conversation->fresh()->unread_user
        ));

        return response()->json([
            'success' => true,
            'message' => $message,
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
}