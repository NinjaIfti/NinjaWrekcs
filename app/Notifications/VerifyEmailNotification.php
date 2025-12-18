<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyEmailNotification extends BaseVerifyEmail
{
    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('Verify Your Email - NinjaWrekcs')
            ->greeting('Welcome to NinjaWrekcs! 🎮')
            ->line('Thank you for signing up! We\'re excited to have you join our Valorant collectibles community.')
            ->line('Before you can start shopping, please verify your email address by clicking the button below:')
            ->action('Verify Email Address', $verificationUrl)
            ->line('This verification link will expire in 60 minutes.')
            ->line('If you didn\'t create an account, no further action is required.')
            ->salutation('Happy Shopping!  
The NinjaWrekcs Team');
    }
}
