<?php

namespace App\Notifications;

use App\Models\ChatConversation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewChatMessageForUser extends Notification
{
    use Queueable;

    public function __construct(
        public ChatConversation $conversation
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Message from BrandX Support')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('You have a new message from BrandX Support.')
            ->line('Click the button below to view and reply.')
            ->action('Open Support Chat', url('/dashboard'))
            ->line('Please log in to view the message content.')
            ->salutation('Regards, BrandX Team');
    }
}