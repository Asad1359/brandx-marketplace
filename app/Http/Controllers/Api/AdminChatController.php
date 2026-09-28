<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminChatController extends Controller
{
    /**
     * Get all conversations.
     */
    public function getAllChats()
    {
        $chats = ChatConversation::with([
            'user:id,name,email'
        ])
            ->withCount([
                'messages as total_messages'
            ])
            ->orderByDesc('updated_at')
            ->get();

        return response()->json([
            'success' => true,
            'chats' => $chats,
        ]);
    }


    /**
     * Get conversation with a specific user.
     */
    public function getConversation($userId)
    {
        $conversation = ChatConversation::where(
            'user_id',
            $userId
        )->first();

        if (!$conversation) {
            return response()->json([
                'success' => true,
                'conversation_id' => null,
                'messages' => [],
            ]);
        }

        $messages = $conversation->messages()
            ->orderBy('id', 'asc')
            ->get();

        $conversation->update([
            'unread_admin' => 0,
        ]);

        $conversation->messages()
            ->where('sender_type', 'user')
            ->where('is_read', false)
            ->update([
                'is_read' => true,
            ]);

        return response()->json([
            'success' => true,
            'conversation_id' => $conversation->id,
            'messages' => $messages,
        ]);
    }


    /**
     * Send message from admin to user.
     */
    public function sendMessage(Request $request)
    {
        $admin = Auth::user();

        $validated = $request->validate([
            'user_id' => [
                'required',
                'integer',
                'exists:users,id',
            ],

            'message' => [
                'required',
                'string',
                'max:5000',
            ],
        ]);

        $conversation = ChatConversation::firstOrCreate(
            [
                'user_id' => $validated['user_id'],
            ],
            [
                'last_message' => null,
                'unread_admin' => 0,
                'unread_user' => 0,
            ]
        );

        $message = ChatMessage::create([
            'conversation_id' => $conversation->id,
            'sender_type' => 'admin',
            'sender_id' => $admin->id,
            'message' => $validated['message'],
            'is_read' => false,
        ]);

        $conversation->increment('unread_user');

        $conversation->update([
            'last_message' => $validated['message'],
        ]);

        return response()->json([
            'success' => true,
            'message' => $message,
        ], 201);
    }
}