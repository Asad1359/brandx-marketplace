<?php

namespace Database\Seeders;

use App\Models\ChatBotReply;
use Illuminate\Database\Seeder;

class ChatBotReplySeeder extends Seeder
{
    public function run(): void
    {
        $replies = [
            ['keyword' => 'hello', 'reply' => 'Hello! How can we help you today?', 'priority' => 1],
            ['keyword' => 'hi', 'reply' => 'Hi there! What can we do for you?', 'priority' => 1],
            ['keyword' => 'price', 'reply' => 'For pricing details, please visit our marketplace page.', 'priority' => 5],
            ['keyword' => 'help', 'reply' => 'Our team is here to help. Please describe your issue.', 'priority' => 1],
            ['keyword' => 'thank', 'reply' => 'You are welcome! Anything else we can do?', 'priority' => 1],
            ['keyword' => 'bye', 'reply' => 'Goodbye! Have a great day.', 'priority' => 1],
            ['keyword' => 'order', 'reply' => 'For order tracking, please share your order ID.', 'priority' => 5],
        ];

        foreach ($replies as $reply) {
            ChatBotReply::create($reply);
        }
    }
}