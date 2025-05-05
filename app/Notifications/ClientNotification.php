<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ClientNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $subject;
    protected $message;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $subject, string $message)
    {
        $this->subject = $subject;
        $this->message = $message;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['mail']; // SMS channel to be added later
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->subject)
            ->markdown('notifications.client', [
                'clientName' => $notifiable->name,
                'messageContent' => $this->message,
                'companyName' => config('app.name', 'Morocco Car Rentals'),
                'logoUrl' => asset('storage/logo.png'),
                'year' => now()->year,
            ]);
    }

    /**
     * Get the SMS representation of the notification (placeholder).
     */
    public function toSms($notifiable): string
    {
        // Placeholder for SMS provider integration (e.g., Twilio)
        return $this->message;
    }
}