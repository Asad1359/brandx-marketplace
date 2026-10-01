<?php

namespace App\Models;

/**
 * Alias for ChatConversationMember.
 *
 * Explicitly sets the table name because Laravel would otherwise
 * derive 'conversation_members' from the class name.
 */
class ConversationMember extends ChatConversationMember
{
    protected $table = 'chat_conversation_members';
}