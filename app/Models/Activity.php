<?php

namespace App\Models;

use App\Helpers\LocationHelper;
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

        // Get IP address
        $ipAddress = request()->ip();
        
        // Add browser and device information to properties
        if (request()->header('User-Agent')) {
            $userAgentInfo = LocationHelper::parseUserAgent(request()->header('User-Agent'));
            $properties['user_agent'] = $userAgentInfo['full'];
            $properties['browser'] = $userAgentInfo['browser'];
            $properties['os'] = $userAgentInfo['os'];
            $properties['device_type'] = $userAgentInfo['device'];
        }
        
        // Try to get location information from IP
        if (!isset($properties['location'])) {
            $location = LocationHelper::getLocationFromIp($ipAddress);
            if ($location) {
                $properties['location'] = $location;
            }
        }
        
        // Add request URL and method if not provided
        if (!isset($properties['request_url']) && request()->fullUrl()) {
            $properties['request_url'] = request()->fullUrl();
        }
        
        if (!isset($properties['request_method']) && request()->method()) {
            $properties['request_method'] = request()->method();
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
            'ip_address' => $ipAddress,
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
    
    /**
     * Scope a query to only include activities within a given date range.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $startDate
     * @param string $endDate
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInDateRange($query, $startDate, $endDate)
    {
        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }
        
        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }
        
        return $query;
    }
    
    /**
     * Get the display version of the user's name.
     *
     * @return string
     */
    public function getUserNameAttribute()
    {
        if (!$this->user_id) {
            return 'System';
        }
        
        if ($this->user) {
            return $this->user->name;
        }
        
        return 'Unknown User';
    }
    
    /**
     * Get the browser name from properties.
     *
     * @return string
     */
    public function getBrowserAttribute()
    {
        return $this->properties['browser'] ?? 'Unknown';
    }
    
    /**
     * Get the OS from properties.
     *
     * @return string
     */
    public function getOsAttribute()
    {
        return $this->properties['os'] ?? 'Unknown';
    }
    
    /**
     * Get the device type from properties.
     *
     * @return string
     */
    public function getDeviceTypeAttribute()
    {
        return $this->properties['device_type'] ?? 'Unknown';
    }
    
    /**
     * Get the location from properties.
     *
     * @return string
     */
    public function getLocationAttribute()
    {
        return $this->properties['location'] ?? 'Unknown';
    }
}