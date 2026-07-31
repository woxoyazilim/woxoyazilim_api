<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewLeadMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  array  $lead  LeadMailService tarafından normalize edilmiş talep verisi
     */
    public function __construct(public array $lead)
    {
    }

    public function envelope(): Envelope
    {
        $label = $this->lead['type_label'] ?? 'Yeni Talep';
        $name = $this->lead['name'] ?? 'İsimsiz';

        $envelope = new Envelope(
            subject: "[{$label}] {$name}",
        );

        // Ziyaretçinin adresi geçerliyse "Yanıtla" doğrudan ona gitsin
        if (!empty($this->lead['email']) && filter_var($this->lead['email'], FILTER_VALIDATE_EMAIL)) {
            $envelope->replyTo(new Address($this->lead['email'], (string) ($this->lead['name'] ?? '')));
        }

        return $envelope;
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.new-lead',
            with: ['lead' => $this->lead],
        );
    }
}
