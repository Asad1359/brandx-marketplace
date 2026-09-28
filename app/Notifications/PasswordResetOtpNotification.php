<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordResetOtpNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected string $otp
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('BrandX Password Reset OTP')
            ->greeting('Hello ' . ($notifiable->name ?? 'User') . '!')
            ->line('You requested to reset your BrandX account password.')
            ->line('Your password reset OTP is:')
            ->line($this->otp)
            ->line('This OTP will expire in 10 minutes.')
            ->line('If you did not request this password reset, you can ignore this email.')
            ->salutation('Regards, BrandX Team');
    }
}