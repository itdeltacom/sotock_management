<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Newsletter extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject',
        'content',
        'attachment',
        'status',
        'sent_at',
        'scheduled_for',
        'recipients_count',
        'open_count',
        'click_count',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'scheduled_for' => 'datetime',
        'recipients_count' => 'integer',
        'open_count' => 'integer',
        'click_count' => 'integer',
    ];

    /**
     * The possible statuses for a newsletter.
     */
    public const STATUS_DRAFT = 'draft';
    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_SENDING = 'sending';
    public const STATUS_SENT = 'sent';
    public const STATUS_FAILED = 'failed';

    /**
     * Scope a query to only include draft newsletters.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    /**
     * Scope a query to only include scheduled newsletters.
     */
    public function scopeScheduled($query)
    {
        return $query->where('status', self::STATUS_SCHEDULED)
            ->whereNotNull('scheduled_for');
    }

    /**
     * Scope a query to only include sent newsletters.
     */
    public function scopeSent($query)
    {
        return $query->where('status', self::STATUS_SENT)
            ->whereNotNull('sent_at');
    }

    /**
     * Get attachment URL if it exists
     */
    public function getAttachmentUrlAttribute()
    {
        if ($this->attachment) {
            return Storage::url($this->attachment);
        }
        return null;
    }
}