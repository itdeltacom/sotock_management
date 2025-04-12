<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewsletterSubscriber extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'is_active',
        'confirmation_token',
        'confirmed_at',
        'unsubscribed_at',
        'last_email_sent_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'confirmed_at' => 'datetime',
        'unsubscribed_at' => 'datetime',
        'last_email_sent_at' => 'datetime',
    ];

    /**
     * Scope a query to only include active subscribers.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->whereNotNull('confirmed_at')
            ->whereNull('unsubscribed_at');
    }

    /**
     * Scope a query to only include unconfirmed subscribers.
     */
    public function scopeUnconfirmed($query)
    {
        return $query->whereNull('confirmed_at');
    }

    /**
     * Scope a query to only include unsubscribed.
     */
    public function scopeUnsubscribed($query)
    {
        return $query->whereNotNull('unsubscribed_at');
    }
}