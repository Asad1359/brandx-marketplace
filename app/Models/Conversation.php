<?php

namespace App\Models;

/**
 * Alias for ChatConversation.
 *
 * Explicitly sets the table name because Laravel would otherwise
 * derive 'conversations' from the class name.
 */
class Conversation extends ChatConversation
{
    protected $table = 'chat_conversations';
}