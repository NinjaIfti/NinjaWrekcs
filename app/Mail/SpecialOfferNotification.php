<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SpecialOfferNotification extends Mailable
{
    use Queueable, SerializesModels;

    public string $title;
    public string $message;
    public string $url;

    public function __construct(string $title, string $message, ?string $url = null)
    {
        $this->title = (string) $title;
        $this->message = (string) $message;
        $this->url = $url !== null && $url !== '' ? (string) $url : route('shop.index');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🎁 ' . $this->title . ' - NinjaWrecks',
            from: new \Illuminate\Mail\Mailables\Address(
                config('mail.from.address'),
                config('mail.from.name')
            ),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.special-offer-notification',
        );
    }
}
