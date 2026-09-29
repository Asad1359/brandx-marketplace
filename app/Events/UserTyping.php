<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserTyping implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $userId,
        public string $senderType,
        public string $userName,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chat.user.' . $this->userId),
            new PrivateChannel('chat.admin'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'user.typing';
    }

    public function broadcastWith(): array
    {
        return [
            'user_id' => $this->userId,
            'sender_type' => $this->senderType,
            'user_name' => $this->userName,
        ];
    }
}