<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserPasswordChangedNotification extends Notification
{
    use Queueable;

    protected string $newPassword;

    public function __construct(string $newPassword)
    {
        $this->newPassword = $newPassword;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('BrandX - Your Password Has Been Changed')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line(
                'Your BrandX account password has been changed by an administrator.'
            )
            ->line(
                'Your new temporary password is:'
            )
            ->line(
                $this->newPassword
            )
            ->line(
                'Please use this password to log in to your account.'
            )
            ->line(
                'For security, change your password after logging in.'
            )
            ->salutation('Regards, BrandX Team');
    }
}