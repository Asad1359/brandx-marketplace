<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatBotReply extends Model
{
    protected $fillable = ['keyword', 'reply', 'priority', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
        'priority' => 'integer',
    ];

    public static function findReply(string $message): ?string
    {
        $message = strtolower(trim($message));

        $replies = static::where('is_active', true)
            ->orderByDesc('priority')
            ->get();

        foreach ($replies as $botReply) {
            if (stripos($message, strtolower($botReply->keyword)) !== false) {
                return $botReply->reply;
            }
        }

        return null;
    }
}