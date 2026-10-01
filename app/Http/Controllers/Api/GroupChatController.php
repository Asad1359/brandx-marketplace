<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\ChatConversation;
use App\Models\ChatConversationMember;
use App\Models\ChatMessage;
use App\Models\User;

use App\Events\MessageSent;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class GroupChatController extends Controller
{
    /*
    |==========================================================================
    | GET /api/chat/groups
    | List all groups user belongs to
    |==========================================================================
    */
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        $groups = ChatConversation::query()
            ->where('type', 'group')
            ->whereHas('members', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->withCount('members')
            ->with(['lastMessage'])
            ->orderByDesc('updated_at')
            ->get()
            ->map(function ($group) {
                return [
                    'id'            => $group->id,
                    'name'          => $group->name,
                    'members_count' => $group->members_count,
                    'last_message'  => optional($group->lastMessage)->message,
                    'updated_at'    => $group->updated_at,
                ];
            });

        return response()->json([
            'success' => true,
            'groups'  => $groups,
        ]);
    }

    /*
    |==========================================================================
    | POST /api/chat/groups
    | Create a new group (creator becomes admin)
    |==========================================================================
    */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
        ]);

        $user = $request->user();

        DB::beginTransaction();

        try {
            $group = ChatConversation::create([
                'type'       => 'group',
                'name'       => $validated['name'],
                'user_id'    => $user->id,          // ⭐ owner
                'created_by' => $user->id,
            ]);

            ChatConversationMember::create([
                'conversation_id' => $group->id,
                'user_id'         => $user->id,
                'role'            => 'admin',
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Unable to create group.',
                'error'   => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'success' => true,
            'group'   => [
                'id'   => $group->id,
                'name' => $group->name,
            ],
        ], 201);
    }

    /*
    |==========================================================================
    | GET /api/chat/groups/{id}
    | Group details with members
    |==========================================================================
    */
    public function show(Request $request, $id)
    {
        $group = $this->findGroupForUser($request->user()->id, $id);

        if (!$group) {
            return response()->json([
                'success' => false,
                'message' => 'Group not found.',
            ], 404);
        }

        $group->load(['members' => function ($q) {
            $q->select('users.id', 'users.name', 'users.email');
        }]);

        return response()->json([
            'success' => true,
            'group'   => [
                'id'      => $group->id,
                'name'    => $group->name,
                'members' => $group->members->map(function ($m) {
                    return [
                        'id'    => $m->id,
                        'name'  => $m->name,
                        'email' => $m->email,
                        'pivot' => [
                            'role' => $m->pivot->role,
                        ],
                    ];
                }),
            ],
        ]);
    }

    /*
    |==========================================================================
    | GET /api/chat/groups/{id}/messages
    |==========================================================================
    */
    public function messages(Request $request, $id)
    {
        $group = $this->findGroupForUser($request->user()->id, $id);

        if (!$group) {
            return response()->json([
                'success' => false,
                'message' => 'Group not found.',
            ], 404);
        }

        $messages = ChatMessage::query()
            ->where('conversation_id', $group->id)
            ->with(['sender', 'parent.sender'])
            ->orderBy('created_at')
            ->get()
            ->map(fn($m) => $this->formatMessage($m));

        $group->load(['members' => function ($q) {
            $q->select('users.id', 'users.name', 'users.email');
        }]);

        return response()->json([
            'success'  => true,
            'messages' => $messages,
            'group'    => [
                'id'      => $group->id,
                'name'    => $group->name,
                'members' => $group->members->map(function ($m) {
                    return [
                        'id'    => $m->id,
                        'name'  => $m->name,
                        'email' => $m->email,
                        'pivot' => [
                            'role' => $m->pivot->role,
                        ],
                    ];
                }),
            ],
        ]);
    }

    /*
    |==========================================================================
    | POST /api/chat/groups/{id}/messages
    | Send a message to group
    |==========================================================================
    */
    public function sendMessage(Request $request, $id)
    {
        $group = $this->findGroupForUser($request->user()->id, $id);

        if (!$group) {
            return response()->json([
                'success' => false,
                'message' => 'Group not found.',
            ], 404);
        }

        $request->validate([
            'message'    => 'nullable|string|max:5000',
            'attachment' => 'nullable|file|max:20480',
            'parent_id'  => 'nullable|integer|exists:chat_messages,id',
        ]);

        if (!$request->filled('message') && !$request->hasFile('attachment')) {
            return response()->json([
                'success' => false,
                'message' => 'Message or attachment is required.',
            ], 422);
        }

        $user = $request->user();

        $data = [
            'conversation_id' => $group->id,
            'sender_id'       => $user->id,
            'sender_type'     => 'user',
            'message'         => $request->input('message'),
            'parent_id'       => $request->input('parent_id'),
        ];

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

        // ⭐ Conversation update
        $group->update([
            'last_message'  => $message->message ?: $message->attachment_name,
            'last_reply_at' => now(),
        ]);

        $message->load(['sender', 'parent.sender']);
        $payload = $this->formatMessage($message);

        // Broadcast to all members
        $memberIds = ChatConversationMember::where('conversation_id', $group->id)
            ->where('user_id', '!=', $user->id)
            ->pluck('user_id');

        foreach ($memberIds as $memberId) {
            try {
                broadcast(new MessageSent($payload, $memberId))->toOthers();
            } catch (\Throwable $e) {}
        }

        return response()->json([
            'success' => true,
            'message' => $payload,
        ], 201);
    }

    /*
    |==========================================================================
    | POST /api/chat/groups/{id}/members
    | Add member to group
    |==========================================================================
    */
    public function addMember(Request $request, $id)
    {
        $group = $this->findGroupForUser($request->user()->id, $id);

        if (!$group) {
            return response()->json([
                'success' => false,
                'message' => 'Group not found.',
            ], 404);
        }

        $validated = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
        ]);

        $exists = ChatConversationMember::where('conversation_id', $group->id)
            ->where('user_id', $validated['user_id'])
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'User is already a member.',
            ], 422);
        }

        ChatConversationMember::create([
            'conversation_id' => $group->id,
            'user_id'         => $validated['user_id'],
            'role'            => 'member',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Member added.',
        ]);
    }

    /*
    |==========================================================================
    | DELETE /api/chat/groups/{id}/members/{userId}
    |==========================================================================
    */
    public function removeMember(Request $request, $id, $userId)
    {
        $group = $this->findGroupForUser($request->user()->id, $id);

        if (!$group) {
            return response()->json([
                'success' => false,
                'message' => 'Group not found.',
            ], 404);
        }

        ChatConversationMember::where('conversation_id', $group->id)
            ->where('user_id', $userId)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Member removed.',
        ]);
    }

    /*
    |==========================================================================
    | POST /api/chat/groups/{id}/leave
    |==========================================================================
    */
    public function leave(Request $request, $id)
    {
        $group = $this->findGroupForUser($request->user()->id, $id);

        if (!$group) {
            return response()->json([
                'success' => false,
                'message' => 'Group not found.',
            ], 404);
        }

        ChatConversationMember::where('conversation_id', $group->id)
            ->where('user_id', $request->user()->id)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'You left the group.',
        ]);
    }

    /*
    |==========================================================================
    | HELPERS
    |==========================================================================
    */

    protected function findGroupForUser($userId, $groupId)
    {
        return ChatConversation::query()
            ->where('type', 'group')
            ->where('id', $groupId)
            ->whereHas('members', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->first();
    }

    protected function formatMessage(ChatMessage $message)
    {
        return [
            'id'              => $message->id,
            'conversation_id' => $message->conversation_id,
            'sender_id'       => $message->sender_id,
            'sender_type'     => $message->sender_type,
            'sender'          => $message->sender ? [
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
            'is_image'        => (bool) $message->is_image,
            'is_video'        => (bool) $message->is_video,
            'is_audio'        => (bool) $message->is_audio,
            'is_starred'      => (bool) $message->is_starred,
            'read_at'         => $message->read_at,
            'deleted_at'      => $message->deleted_at,
            'parent'          => $message->parent ? [
                'id'              => $message->parent->id,
                'sender_type'     => $message->parent->sender_type,
                'message'         => $message->parent->message,
                'attachment_name' => $message->parent->attachment_name,
                'sender'          => $message->parent->sender ? [
                    'id'   => $message->parent->sender->id,
                    'name' => $message->parent->sender->name,
                ] : null,
            ] : null,
            'created_at'      => $message->created_at,
        ];
    }
}