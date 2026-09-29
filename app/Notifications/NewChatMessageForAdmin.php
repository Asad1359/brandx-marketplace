<?php

namespace App\Notifications;

use App\Models\ChatConversation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewChatMessageForAdmin extends Notification
{
    use Queueable;

    public function __construct(
        public ChatConversation $conversation,
        public string $senderName,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Customer Message — BrandX')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('You have a new message from a customer.')
            ->line('Customer: ' . $this->senderName)
            ->line('Click the button below to view and reply.')
            ->action('Open Customer Chats', url('/admin/chats'))
            ->line('Please log in to view the message content.')
            ->salutation('Regards, BrandX Team');
    }
}