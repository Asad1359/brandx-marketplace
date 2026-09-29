<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChatMessage extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'conversation_id',
        'parent_message_id',
        'sender_type',
        'sender_id',
        'assigned_to',
        'message',
        'is_read',
        'read_at',
        'attachment_path',
        'attachment_name',
        'attachment_type',
        'attachment_size',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'attachment_size' => 'integer',
    ];

    protected $appends = [
        'attachment_url',
        'is_image',
        'is_video',
        'is_audio',
        'is_pdf',
    ];

    public function conversation()
    {
        return $this->belongsTo(ChatConversation::class, 'conversation_id');
    }

    public function parent()
    {
        return $this->belongsTo(ChatMessage::class, 'parent_message_id');
    }

    public function getAttachmentUrlAttribute(): ?string
    {
        if (!$this->attachment_path) return null;
        return asset('storage/' . $this->attachment_path);
    }

    public function getIsImageAttribute(): bool
    {
        return $this->attachment_type
            && str_starts_with($this->attachment_type, 'image/');
    }

    public function getIsVideoAttribute(): bool
    {
        return $this->attachment_type
            && str_starts_with($this->attachment_type, 'video/');
    }

    public function getIsAudioAttribute(): bool
    {
        return $this->attachment_type
            && str_starts_with($this->attachment_type, 'audio/');
    }

    public function getIsPdfAttribute(): bool
    {
        return $this->attachment_type === 'application/pdf';
    }
}