<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\NewsletterConfirmation;
use Illuminate\Validation\Rule;

class NewsletterController extends Controller
{
    /**
     * Subscribe a user to the newsletter.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function subscribe(Request $request)
    {
        // Validate the email
        $request->validate([
            'email' => 'required|email|max:255',
        ]);
        
        $email = $request->email;
        
        // Check if the email is already subscribed and confirmed
        $existing = NewsletterSubscriber::where('email', $email)->first();
        
        if ($existing) {
            // If already active, return error message
            if ($existing->is_active && $existing->confirmed_at) {
                return response()->json([
                    'success' => false,
                    'message' => 'This email is already subscribed to our newsletter.'
                ], 422);
            }
            
            // If unsubscribed, reactivate
            if ($existing->unsubscribed_at) {
                $existing->update([
                    'is_active' => true,
                    'unsubscribed_at' => null,
                    'confirmation_token' => Str::random(60),
                ]);
                
                // Send confirmation email
                try {
                    Mail::to($email)->send(new NewsletterConfirmation($existing));
                } catch (\Exception $e) {
                    report($e);
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to send confirmation email. Please try again later.'
                    ], 500);
                }
                
                return response()->json([
                    'success' => true,
                    'message' => 'Thank you for resubscribing! Please check your email to confirm your subscription.'
                ]);
            }
            
            // If not confirmed yet, resend confirmation email
            if (!$existing->confirmed_at) {
                // Generate new token
                $existing->update([
                    'confirmation_token' => Str::random(60)
                ]);
                
                // Send confirmation email
                try {
                    Mail::to($email)->send(new NewsletterConfirmation($existing));
                } catch (\Exception $e) {
                    report($e);
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to send confirmation email. Please try again later.'
                    ], 500);
                }
                
                return response()->json([
                    'success' => true,
                    'message' => 'Please check your email to confirm your subscription.'
                ]);
            }
        }
        
        // Create new subscriber
        try {
            $subscriber = NewsletterSubscriber::create([
                'email' => $email,
                'is_active' => true,
                'confirmation_token' => Str::random(60),
            ]);
            
            // Send confirmation email
            try {
                Mail::to($email)->send(new NewsletterConfirmation($subscriber));
            } catch (\Exception $e) {
                report($e);
                // Delete the subscriber if email fails
                $subscriber->delete();
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to send confirmation email. Please try again later.'
                ], 500);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Thank you for subscribing! Please check your email to confirm your subscription.'
            ]);
        } catch (\Exception $e) {
            report($e);
            
            // Check for duplicate entry error
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                return response()->json([
                    'success' => false,
                    'message' => 'This email is already subscribed to our newsletter.'
                ], 422);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to subscribe. Please try again later.'
            ], 500);
        }
    }
    
    /**
     * Confirm a subscription with token.
     *
     * @param  string  $token
     * @return \Illuminate\Http\Response
     */
    public function confirm($token)
    {
        $subscriber = NewsletterSubscriber::where('confirmation_token', $token)->first();
        
        if (!$subscriber) {
            return redirect()->route('home')
                ->with('error', 'Invalid confirmation link.');
        }
        
        // Confirm subscription
        $subscriber->update([
            'confirmed_at' => now(),
            'confirmation_token' => null,
        ]);
        
        return redirect()->route('home')
            ->with('success', 'Your subscription has been confirmed! Thank you for subscribing to our newsletter.');
    }
    
    /**
     * Unsubscribe a user from the newsletter.
     *
     * @param  string  $email
     * @param  string  $token
     * @return \Illuminate\Http\Response
     */
    public function unsubscribe($email, $token)
    {
        // Decrypt and validate email
        try {
            $email = decrypt($email);
        } catch (\Exception $e) {
            return redirect()->route('home')
                ->with('error', 'Invalid unsubscribe link.');
        }
        
        $subscriber = NewsletterSubscriber::where('email', $email)
            ->where(function($query) use ($token) {
                $query->where('confirmation_token', $token)
                    ->orWhere('id', $token); // Alternative way to identify
            })
            ->first();
        
        if (!$subscriber) {
            return redirect()->route('home')
                ->with('error', 'Invalid unsubscribe link.');
        }
        
        // Unsubscribe
        $subscriber->update([
            'is_active' => false,
            'unsubscribed_at' => now(),
        ]);
        
        return redirect()->route('home')
            ->with('success', 'You have been successfully unsubscribed from our newsletter.');
    }
}