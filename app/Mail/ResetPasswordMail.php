<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;
    
    public $resetLink;
    public $appUrl;

    /**
     * Create a new message instance.
     *
     * @param string $resetLink
     * @return void
     */
    public function __construct($resetLink)
    {
        $this->resetLink = $resetLink;
        $this->appUrl = config('app.url');
        
        // Log for debugging
        Log::info('ResetPasswordMail constructor called', [
            'resetLink' => $resetLink,
            'appUrl' => $this->appUrl
        ]);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        // Log building the email
        Log::info('Building reset password email');
        
        try {
            $email = $this->subject('Reset Your Admin Password')
                        ->view('admin.emails.reset-password');
                        
            // Log success
            Log::info('Reset password email built successfully');
            
            return $email;
        } catch (\Exception $e) {
            // Log any errors
            Log::error('Failed to build reset password email', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
}