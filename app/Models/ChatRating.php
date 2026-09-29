<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatRating extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'user_id',
        'rating',
        'feedback',
    ];

    public function conversation()
    {
        return $this->belongsTo(ChatConversation::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}