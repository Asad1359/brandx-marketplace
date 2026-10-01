<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\ChatConversation;
use App\Models\ChatConversationMember;
use App\Models\ChatMessage;
use App\Models\CannedResponse;
use App\Models\User;

use App\Events\MessageSent;
use App\Events\MessageDeleted;
use App\Events\MessageDeletedForMe;
use App\Events\MessageRead;
use App\Events\MessageStarred;
use App\Events\UserTyping;
use App\Events\ConversationArchived;
use App\Events\ConversationBlocked;
use App\Events\ConversationCleared;
use App\Events\ConversationDeleted;
use App\Events\MessageAssigned;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;


class AdminChatController extends Controller
{
    /*
    |==========================================================================
    | LIST CONVERSATIONS
    |==========================================================================
    | GET /api/admin/chat/conversations
    |
    | Query params:
    |   ?status=open|blocked|archived
    |   ?assigned_to={userId}
    |   ?search={keyword}
    |   ?page={n}
    |   ?per_page=20
    |==========================================================================
    */

    public function conversations(Request $request): JsonResponse
    {
        $query = ChatConversation::query()
            ->with([
                'user:id,name,email',
                'assignee:id,name,email',
                'members:id,name,email',
                'lastMessage',
            ]);

        /*
        |----------------------------------------------------------------------
        | Filter by status
        |----------------------------------------------------------------------
        */

        if ($request->filled('status')) {

            $status = $request->query('status');

            if ($status === 'archived') {
                $query->where('is_archived', true);
            } elseif ($status === 'blocked') {
                $query->where('is_blocked', true);
            } elseif ($status === 'open') {
                $query->where('is_archived', false)
                      ->where('is_blocked', false);
            }
        }

        /*
        |----------------------------------------------------------------------
        | Filter by assigned admin
        |----------------------------------------------------------------------
        */

        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->query('assigned_to'));
        }

        /*
        |----------------------------------------------------------------------
        | Search by user name/email or conversation name
        |----------------------------------------------------------------------
        */

        if ($request->filled('search')) {

            $keyword = $request->query('search');

            $query->where(function ($q) use ($keyword) {

                $q->where('name', 'like', "%{$keyword}%")
                  ->orWhereHas('user', function ($u) use ($keyword) {
                      $u->where('name', 'like', "%{$keyword}%")
                        ->orWhere('email', 'like', "%{$keyword}%");
                  })
                  ->orWhereHas('members', function ($m) use ($keyword) {
                      $m->where('name', 'like', "%{$keyword}%")
                        ->orWhere('email', 'like', "%{$keyword}%");
                  });
            });
        }

        /*
        |----------------------------------------------------------------------
        | Paginate
        |----------------------------------------------------------------------
        */

        $perPage = (int) $request->query('per_page', 20);

        $conversations = $query
            ->orderByDesc('updated_at')
            ->paginate($perPage);

        /*
        |----------------------------------------------------------------------
        | Transform
        |----------------------------------------------------------------------
        */

        $conversations->getCollection()->transform(function ($c) {
            return $this->formatConversation($c);
        });

        return response()->json([
            'success'       => true,
            'conversations' => $conversations,
        ]);
    }


    /*
    |==========================================================================
    | SHOW SINGLE CONVERSATION
    |==========================================================================
    | GET /api/admin/chat/conversations/{id}
    |==========================================================================
    */

    public function show(Request $request, $id): JsonResponse
    {
        $conversation = ChatConversation::with([
            'user:id,name,email',
            'assignee:id,name,email',
            'members:id,name,email',
        ])->find($id);

        if (! $conversation) {
            return response()->json([
                'success' => false,
                'message' => 'Conversation not found.',
            ], 404);
        }

        $messages = ChatMessage::query()
            ->where('conversation_id', $conversation->id)
            ->with(['sender:id,name,email', 'parent.sender:id,name,email'])
            ->orderBy('created_at')
            ->get()
            ->map(fn ($m) => $this->formatMessage($m));

        return response()->json([
            'success'      => true,
            'conversation' => $this->formatConversation($conversation),
            'messages'     => $messages,
        ]);
    }


    /*
    |==========================================================================
    | LIST MESSAGES ONLY
    |==========================================================================
    | GET /api/admin/chat/conversations/{id}/messages
    |==========================================================================
    */

    public function messages(Request $request, $id): JsonResponse
    {
        $conversation = ChatConversation::find($id);

        if (! $conversation) {
            return response()->json([
                'success' => false,
                'message' => 'Conversation not found.',
            ], 404);
        }

        $messages = ChatMessage::query()
            ->where('conversation_id', $conversation->id)
            ->with(['sender:id,name,email', 'parent.sender:id,name,email'])
            ->orderBy('created_at')
            ->get()
            ->map(fn ($m) => $this->formatMessage($m));

        return response()->json([
            'success'  => true,
            'messages' => $messages,
        ]);
    }


    /*
    |==========================================================================
    | SEND ADMIN MESSAGE
    |==========================================================================
    | POST /api/admin/chat/conversations/{id}/messages
    |==========================================================================
    */

    public function sendMessage(Request $request, $id): JsonResponse
    {
        $conversation = ChatConversation::find($id);

        if (! $conversation) {
            return response()->json([
                'success' => false,
                'message' => 'Conversation not found.',
            ], 404);
        }

        /*
        |----------------------------------------------------------------------
        | Blocked check
        |----------------------------------------------------------------------
        */

        if ($conversation->is_blocked) {
            return response()->json([
                'success' => false,
                'message' => 'Conversation is blocked.',
            ], 423);
        }

        /*
        |----------------------------------------------------------------------
        | Validation
        |----------------------------------------------------------------------
        */

        $request->validate([
            'message'    => 'nullable|string|max:5000',
            'attachment' => 'nullable|file|max:20480',
            'parent_id'  => 'nullable|integer|exists:chat_messages,id',
        ]);

        if (
            ! $request->filled('message') &&
            ! $request->hasFile('attachment')
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Message or attachment is required.',
            ], 422);
        }

        $admin = $request->user();

        $data = [
            'conversation_id'   => $conversation->id,
            'parent_message_id' => $request->input('parent_id'),
            'sender_id'         => $admin->id,
            'sender_type'       => 'admin',
            'message'           => $request->input('message'),
        ];

        /*
        |----------------------------------------------------------------------
        | Attachment handling
        |----------------------------------------------------------------------
        */

        if ($request->hasFile('attachment')) {

            $file = $request->file('attachment');
            $path = $file->store('chat/attachments', 'public');

            $mime = $file->getMimeType();

            $data['attachment_path'] = $path;
            $data['attachment_name'] = $file->getClientOriginalName();
            $data['attachment_size'] = $file->getSize();
            $data['attachment_type'] = $mime;
        }

        $message = ChatMessage::create($data);

        /*
        |----------------------------------------------------------------------
        | Update conversation preview
        |----------------------------------------------------------------------
        */

        $conversation->update([
            'last_message'   => $message->message
                ?: $message->attachment_name,
            'last_reply_at'  => now(),
            'unread_user'    => $conversation->unread_user + 1,
        ]);

        /*
        |----------------------------------------------------------------------
        | Load relations and format
        |----------------------------------------------------------------------
        */

        $message->load(['sender:id,name,email']);

        $payload = $this->formatMessage($message);

        /*
        |----------------------------------------------------------------------
        | Broadcast to user + other admins
        |----------------------------------------------------------------------
        */

        if ($conversation->user_id) {
            broadcast(new MessageSent(
                $payload,
                $conversation->user_id
            ))->toOthers();
        }

        broadcast(new MessageSent($payload, 0))
            ->toOthers();

        return response()->json([
            'success' => true,
            'message' => $payload,
        ], 201);
    }


    /*
    |==========================================================================
    | MARK AS READ
    |==========================================================================
    | POST /api/admin/chat/conversations/{id}/read
    |==========================================================================
    */

    public function markAsRead(Request $request, $id): JsonResponse
    {
        $conversation = ChatConversation::find($id);

        if (! $conversation) {
            return response()->json([
                'success' => false,
                'message' => 'Conversation not found.',
            ], 404);
        }

        /*
        |----------------------------------------------------------------------
        | Mark unread user messages as read
        |----------------------------------------------------------------------
        */

        ChatMessage::query()
            ->where('conversation_id', $conversation->id)
            ->where('sender_type', 'user')
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        $conversation->update(['unread_admin' => 0]);

        return response()->json([
            'success' => true,
        ]);
    }


    /*
    |==========================================================================
    | TYPING INDICATOR
    |==========================================================================
    | POST /api/admin/chat/conversations/{id}/typing
    |==========================================================================
    */

    public function typing(Request $request, $id): JsonResponse
    {
        $conversation = ChatConversation::find($id);

        if (! $conversation) {
            return response()->json([
                'success' => false,
                'message' => 'Conversation not found.',
            ], 404);
        }

        $admin = $request->user();

        if ($conversation->user_id) {
            broadcast(new UserTyping(
                (int) $conversation->id,
                $admin->id,
                $admin->name,
                $conversation->user_id
            ))->toOthers();
        }

        return response()->json([
            'success' => true,
        ]);
    }


    /*
    |==========================================================================
    | CANNED RESPONSES
    |==========================================================================
    | GET /api/admin/chat/canned-responses
    |==========================================================================
    */

    public function cannedResponses(): JsonResponse
    {
        $responses = CannedResponse::orderBy('title')->get();

        return response()->json([
            'success'   => true,
            'responses' => $responses,
        ]);
    }


    /*
    |==========================================================================
    | SEARCH MESSAGES
    |==========================================================================
    | GET /api/admin/chat/search?q=
    |==========================================================================
    */

    public function search(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => 'required|string|min:2|max:100',
        ]);

        $results = ChatMessage::query()
            ->whereNull('deleted_at')
            ->where('message', 'like', '%' . $validated['q'] . '%')
            ->with(['sender:id,name,email', 'conversation:id,user_id,name'])
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->map(function ($m) {
                return [
                    'id'              => $m->id,
                    'conversation_id' => $m->conversation_id,
                    'conversation'    => $m->conversation ? [
                        'id'   => $m->conversation->id,
                        'name' => $m->conversation->name,
                    ] : null,
                    'sender'          => $m->sender ? [
                        'id'   => $m->sender->id,
                        'name' => $m->sender->name,
                    ] : null,
                    'message'         => $m->message,
                    'created_at'      => $m->created_at,
                ];
            });

        return response()->json([
            'success' => true,
            'results' => $results,
        ]);
    }


    /*
    |==========================================================================
    | ASSIGN CONVERSATION
    |==========================================================================
    | POST /api/admin/chat/conversations/{id}/assign
    |
    | Payload: { admin_id }
    |==========================================================================
    */

    public function assign(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'admin_id' => 'required|integer|exists:users,id',
        ]);

        $conversation = ChatConversation::find($id);

        if (! $conversation) {
            return response()->json([
                'success' => false,
                'message' => 'Conversation not found.',
            ], 404);
        }

        $conversation->assigned_to = $validated['admin_id'];
        $conversation->save();

        broadcast(new MessageAssigned($conversation))->toOthers();

        return response()->json([
            'success' => true,
            'message' => 'Conversation assigned.',
        ]);
    }


    /*
    |==========================================================================
    | BLOCK CONVERSATION
    |==========================================================================
    | POST /api/admin/chat/conversations/{id}/block
    |
    | Payload: { reason? }
    |==========================================================================
    */

    public function block(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $conversation = ChatConversation::find($id);

        if (! $conversation) {
            return response()->json([
                'success' => false,
                'message' => 'Conversation not found.',
            ], 404);
        }

        $conversation->update([
            'is_blocked'     => true,
            'blocked_at'     => now(),
            'blocked_reason' => $validated['reason'] ?? null,
        ]);

        broadcast(new ConversationBlocked(
            $conversation,
            $request->user()->name
        ))->toOthers();

        return response()->json([
            'success' => true,
            'message' => 'Conversation blocked.',
        ]);
    }


    /*
    |==========================================================================
    | UNBLOCK CONVERSATION
    |==========================================================================
    | POST /api/admin/chat/conversations/{id}/unblock
    |==========================================================================
    */

    public function unblock(Request $request, $id): JsonResponse
    {
        $conversation = ChatConversation::find($id);

        if (! $conversation) {
            return response()->json([
                'success' => false,
                'message' => 'Conversation not found.',
            ], 404);
        }

        $conversation->update([
            'is_blocked'     => false,
            'blocked_at'     => null,
            'blocked_reason' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Conversation unblocked.',
        ]);
    }


    /*
    |==========================================================================
    | CLEAR CONVERSATION MESSAGES
    |==========================================================================
    | POST /api/admin/chat/conversations/{id}/clear
    |==========================================================================
    */

    public function clear(Request $request, $id): JsonResponse
    {
        $conversation = ChatConversation::find($id);

        if (! $conversation) {
            return response()->json([
                'success' => false,
                'message' => 'Conversation not found.',
            ], 404);
        }

        ChatMessage::where('conversation_id', $conversation->id)
            ->forceDelete();

        $conversation->update([
            'last_message'  => null,
            'unread_admin'  => 0,
            'unread_user'   => 0,
        ]);

        broadcast(new ConversationCleared($conversation))->toOthers();

        return response()->json([
            'success' => true,
            'message' => 'Conversation cleared.',
        ]);
    }


    /*
    |==========================================================================
    | ARCHIVE CONVERSATION
    |==========================================================================
    | POST /api/admin/chat/conversations/{id}/archive
    |==========================================================================
    */

    public function archive(Request $request, $id): JsonResponse
    {
        $conversation = ChatConversation::find($id);

        if (! $conversation) {
            return response()->json([
                'success' => false,
                'message' => 'Conversation not found.',
            ], 404);
        }

        $conversation->update([
            'is_archived' => true,
            'archived_at' => now(),
        ]);

        broadcast(new ConversationArchived($conversation))->toOthers();

        return response()->json([
            'success' => true,
            'message' => 'Conversation archived.',
        ]);
    }


    /*
    |==========================================================================
    | UNARCHIVE CONVERSATION
    |==========================================================================
    | POST /api/admin/chat/conversations/{id}/unarchive
    |==========================================================================
    */

    public function unarchive(Request $request, $id): JsonResponse
    {
        $conversation = ChatConversation::find($id);

        if (! $conversation) {
            return response()->json([
                'success' => false,
                'message' => 'Conversation not found.',
            ], 404);
        }

        $conversation->update([
            'is_archived' => false,
            'archived_at' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Conversation unarchived.',
        ]);
    }


    /*
    |==========================================================================
    | DELETE CONVERSATION
    |==========================================================================
    | DELETE /api/admin/chat/conversations/{id}
    |==========================================================================
    */

    public function destroy(Request $request, $id): JsonResponse
    {
        $conversation = ChatConversation::find($id);

        if (! $conversation) {
            return response()->json([
                'success' => false,
                'message' => 'Conversation not found.',
            ], 404);
        }

        $userId = $conversation->user_id;
        $conversationId = $conversation->id;

        ChatMessage::where('conversation_id', $conversation->id)
            ->forceDelete();

        ChatConversationMember::where('conversation_id', $conversation->id)
            ->delete();

        $conversation->delete();

        broadcast(new ConversationDeleted(
            $userId,
            $conversationId
        ))->toOthers();

        return response()->json([
            'success' => true,
            'message' => 'Conversation deleted.',
        ]);
    }


    /*
    |==========================================================================
    | DELETE SINGLE MESSAGE
    |==========================================================================
    | DELETE /api/admin/chat/messages/{id}?scope=all|me
    |==========================================================================
    */

    public function deleteMessage(Request $request, $id): JsonResponse
    {
        $scope = $request->query('scope', 'all');

        $message = ChatMessage::find($id);

        if (! $message) {
            return response()->json([
                'success' => false,
                'message' => 'Message not found.',
            ], 404);
        }

        if ($scope === 'me') {

            $message->update(['deleted_for_sender' => true]);

            broadcast(new MessageDeletedForMe(
                $message,
                'admin'
            ))->toOthers();

        } else {

            $message->delete();

            $conversation = $message->conversation;

            if ($conversation) {
                broadcast(new MessageDeleted(
                    $message->id,
                    $conversation->user_id
                ))->toOthers();
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Message deleted.',
        ]);
    }


    /*
    |==========================================================================
    | TOGGLE STAR ON MESSAGE
    |==========================================================================
    | POST /api/admin/chat/messages/{id}/star
    |==========================================================================
    */

    public function toggleStar(Request $request, $id): JsonResponse
    {
        $message = ChatMessage::find($id);

        if (! $message) {
            return response()->json([
                'success' => false,
                'message' => 'Message not found.',
            ], 404);
        }

        $message->is_starred = ! $message->is_starred;
        $message->starred_at = $message->is_starred ? now() : null;
        $message->save();

        broadcast(new MessageStarred($message))->toOthers();

        return response()->json([
            'success'    => true,
            'is_starred' => (bool) $message->is_starred,
        ]);
    }


    /*
    |==========================================================================
    | RATINGS LIST
    |==========================================================================
    | GET /api/admin/chat/ratings
    |==========================================================================
    */

    public function ratings(): JsonResponse
    {
        $ratings = \App\Models\ChatRating::query()
            ->with([
                'conversation:id,user_id,name',
                'user:id,name,email',
            ])
            ->orderByDesc('created_at')
            ->paginate(20);

        $average = round(
            \App\Models\ChatRating::avg('rating') ?? 0,
            2
        );

        $total = \App\Models\ChatRating::count();

        return response()->json([
            'success'  => true,
            'ratings'  => $ratings,
            'average'  => $average,
            'total'    => $total,
        ]);
    }


    /*
    |==========================================================================
    | HELPERS
    |==========================================================================
    */

    /*
    |--------------------------------------------------------------------------
    | Format a conversation for JSON
    |--------------------------------------------------------------------------
    */

    protected function formatConversation(ChatConversation $c): array
    {
        return [
            'id'            => $c->id,
            'type'          => $c->type,
            'name'          => $c->display_name,
            'avatar'        => $c->avatar_path,
            'is_blocked'    => (bool) $c->is_blocked,
            'is_archived'   => (bool) $c->is_archived,
            'assigned_to'   => $c->assigned_to,
            'last_message'  => $c->last_message,
            'last_reply_at' => $c->last_reply_at,
            'unread_admin'  => (int) $c->unread_admin,
            'unread_user'   => (int) $c->unread_user,
            'updated_at'    => $c->updated_at,

            'user'     => $c->user ? [
                'id'    => $c->user->id,
                'name'  => $c->user->name,
                'email' => $c->user->email,
            ] : null,

            'assignee' => $c->assignee ? [
                'id'    => $c->assignee->id,
                'name'  => $c->assignee->name,
                'email' => $c->assignee->email,
            ] : null,

            'members'  => $c->relationLoaded('members')
                ? $c->members->map(fn ($m) => [
                    'id'    => $m->id,
                    'name'  => $m->name,
                    'email' => $m->email,
                ])
                : [],
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Format a message for JSON / broadcast
    |--------------------------------------------------------------------------
    */

    protected function formatMessage(ChatMessage $message): array
    {
        return [
            'id'              => $message->id,
            'conversation_id' => $message->conversation_id,
            'sender_id'       => $message->sender_id,
            'sender_type'     => $message->sender_type,

            'sender' => $message->sender ? [
                'id'   => $message->sender->id,
                'name' => $message->sender->name,
            ] : null,

            'message'         => $message->message,
            'attachment_url'  => $message->attachment_path
                ? Storage::disk('public')->url($message->attachment_path)
                : null,
            'attachment_name' => $message->attachment_name,
            'attachment_size' => $message->attachment_size,
            'attachment_type' => $message->attachment_type,

            'is_image'   => (bool) $message->is_image,
            'is_video'   => (bool) $message->is_video,
            'is_audio'   => (bool) $message->is_audio,
            'is_pdf'     => (bool) $message->is_pdf,
            'is_starred' => (bool) $message->is_starred,
            'is_read'    => (bool) $message->is_read,

            'read_at'    => $message->read_at,
            'deleted_at' => $message->deleted_at,

            'parent' => $message->parent ? [
                'id'              => $message->parent->id,
                'sender_type'     => $message->parent->sender_type,
                'message'         => $message->parent->message,
                'attachment_name' => $message->parent->attachment_name,
                'sender'          => $message->parent->sender ? [
                    'id'   => $message->parent->sender->id,
                    'name' => $message->parent->sender->name,
                ] : null,
            ] : null,

            'created_at' => $message->created_at,
        ];
    }
}