<?php

namespace App\Mail;

use App\Models\Newsletter;
use App\Models\NewsletterSubscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class NewsletterMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * The newsletter instance.
     *
     * @var \App\Models\Newsletter
     */
    public $newsletter;

    /**
     * The subscriber instance.
     *
     * @var \App\Models\NewsletterSubscriber
     */
    public $subscriber;

    /**
     * Create a new message instance.
     */
    public function __construct(Newsletter $newsletter, NewsletterSubscriber $subscriber)
    {
        $this->newsletter = $newsletter;
        $this->subscriber = $subscriber;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->newsletter->subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        // Generate unsubscribe URL
        $encryptedEmail = encrypt($this->subscriber->email);
        $unsubscribeUrl = route('newsletter.unsubscribe', [
            'email' => $encryptedEmail,
            'token' => $this->subscriber->id
        ]);
        
        return new Content(
            view: 'emails.newsletter',
            with: [
                'content' => $this->newsletter->content,
                'unsubscribeUrl' => $unsubscribeUrl,
                'newsletter' => $this->newsletter,
                'subscriber' => $this->subscriber,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $attachments = [];

        // Add attachment if exists
        if ($this->newsletter->attachment) {
            $path = Storage::path('public/' . $this->newsletter->attachment);
            $filename = basename($path);
            
            $attachments[] = Attachment::fromPath($path)
                ->as($filename)
                ->withMime(mime_content_type($path));
        }
        
        return $attachments;
    }
}