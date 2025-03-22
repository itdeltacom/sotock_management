<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type',
        'title',
        'description',
        'user_id',
        'user_type',
        'subject_id',
        'subject_type',
        'properties',
        'ip_address',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'properties' => 'array',
    ];

    /**
     * Get the user that performed the activity.
     */
    public function user()
    {
        return $this->morphTo();
    }

    /**
     * Get the subject of the activity.
     */
    public function subject()
    {
        return $this->morphTo();
    }

    /**
     * Log activity in the system.
     *
     * @param string $type
     * @param string $title
     * @param string $description
     * @param Model|null $user
     * @param Model|null $subject
     * @param array $properties
     * @return Activity
     */
    public static function log($type, $title, $description, $user = null, $subject = null, $properties = [])
    {
        // If user not provided, try to get from auth
        if ($user === null && auth()->guard('admin')->check()) {
            $user = auth()->guard('admin')->user();
        }

        return static::create([
            'type' => $type,
            'title' => $title,
            'description' => $description,
            'user_id' => $user ? $user->id : null,
            'user_type' => $user ? get_class($user) : null,
            'subject_id' => $subject ? $subject->id : null,
            'subject_type' => $subject ? get_class($subject) : null,
            'properties' => $properties,
            'ip_address' => request()->ip(),
        ]);
    }

    /**
     * Scope a query to only include activities of a given type.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope a query to only include activities for a specific user.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param Model $user
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForUser($query, $user)
    {
        return $query->where('user_id', $user->id)
                     ->where('user_type', get_class($user));
    }

    /**
     * Scope a query to only include activities for a specific subject.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param Model $subject
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForSubject($query, $subject)
    {
        return $query->where('subject_id', $subject->id)
                     ->where('subject_type', get_class($subject));
    }
}