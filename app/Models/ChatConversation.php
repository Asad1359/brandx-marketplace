<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatConversation extends Model
{
    use HasFactory;

    protected $table = 'chat_conversations';

    protected $fillable = [
        'type',
        'name',
        'created_by',
        'avatar_path',
        'user_id',
        'assigned_to',
        'is_blocked',
        'blocked_at',
        'blocked_reason',
        'is_archived',
        'archived_at',
        'last_message',
        'last_reply_at',
        'unread_admin',
        'unread_user',
    ];

    protected $casts = [
        'last_reply_at' => 'datetime',
        'blocked_at'    => 'datetime',
        'archived_at'   => 'datetime',
        'is_blocked'    => 'boolean',
        'is_archived'   => 'boolean',
    ];

    protected $appends = ['is_group', 'member_count', 'display_name'];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function messages()
    {
        return $this->hasMany(ChatMessage::class, 'conversation_id');
    }

    /**
     * ⭐ Latest message of this conversation
     * Yeh relation pehle missing tha — ab add kar diya
     */
    public function lastMessage()
    {
        return $this->hasOne(ChatMessage::class, 'conversation_id')
            ->latestOfMany();
    }

    public function rating()
    {
        return $this->hasOne(ChatRating::class, 'conversation_id');
    }

    public function members()
    {
        return $this->belongsToMany(
            User::class,
            'chat_conversation_members',
            'conversation_id',
            'user_id'
        )
            ->withPivot(['role', 'unread_count', 'last_read_at', 'joined_at'])
            ->withTimestamps();
    }

    public function memberRecords()
    {
        return $this->hasMany(ChatConversationMember::class, 'conversation_id');
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    public function getIsGroupAttribute(): bool
    {
        return $this->type === 'group';
    }

    public function getMemberCountAttribute(): int
    {
        if ($this->type !== 'group') {
            return 2;
        }

        return $this->members()->count();
    }

    public function getDisplayNameAttribute(): string
    {
        if ($this->type === 'group') {
            return $this->name ?: 'Group Chat';
        }

        return $this->user?->name ?? 'User';
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    public function isMember(int $userId): bool
    {
        return $this->memberRecords()
            ->where('user_id', $userId)
            ->exists();
    }

    public function isMemberAdmin(int $userId): bool
    {
        return $this->memberRecords()
            ->where('user_id', $userId)
            ->where('role', 'admin')
            ->exists();
    }
}