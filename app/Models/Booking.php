<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Booking extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'car_id',
        'booking_number',
        'pickup_location',
        'dropoff_location',
        'pickup_date',
        'pickup_time',
        'dropoff_date',
        'dropoff_time',
        'total_days',
        'base_price',
        'discount_amount',
        'tax_amount',
        'total_amount',
        'status',
        'payment_status',
        'payment_method',
        'special_requests',
        'customer_name',
        'customer_email',
        'customer_phone',
        'customer_id_number', // Moroccan ID (CIN or passport)
        'transaction_id',
        'insurance_plan', // Insurance type selected
        'additional_driver', // Additional driver option
        'additional_driver_name',
        'additional_driver_license',
        'delivery_option', // Home/airport delivery
        'delivery_address', // Delivery address if applicable
        'fuel_policy', // Fuel policy (e.g., full-to-full)
        'mileage_limit', // Daily mileage limit
        'extra_mileage_cost', // Cost per extra kilometer
        'deposit_amount', // Security deposit
        'deposit_status', // Deposit payment status
        'notes', // Internal notes for staff
        'cancellation_reason', // Reason for cancellation
        'confirmation_code', // Unique code for customer confirmation
        'language_preference', // Customer's preferred language (Arabic, French, etc.)
        'gps_enabled', // GPS navigation option
        'child_seat', // Child seat option
        'completed_at', // Date/time when booking is completed
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'pickup_date' => 'date',
        'dropoff_date' => 'date',
        'total_days' => 'integer',
        'base_price' => 'float',
        'discount_amount' => 'float',
        'tax_amount' => 'float',
        'total_amount' => 'float',
        'deposit_amount' => 'float',
        'extra_mileage_cost' => 'float',
        'mileage_limit' => 'integer',
        'additional_driver' => 'boolean',
        'gps_enabled' => 'boolean',
        'child_seat' => 'boolean',
        'completed_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<string>
     */
    protected $appends = [
        'formatted_total_amount',
        'status_label',
    ];

    /**
     * Get the car associated with the booking.
     */
    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class);
    }

    /**
     * Get the user associated with the booking.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the payments associated with the booking.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get formatted total amount with currency (MAD).
     */
    public function getFormattedTotalAmountAttribute(): string
    {
        return number_format($this->total_amount, 2) . ' MAD';
    }

    /**
     * Get human-readable status label.
     */
    public function getStatusLabelAttribute(): string
    {
        $labels = [
            'pending' => 'Pending',
            'confirmed' => 'Confirmed',
            'in_progress' => 'In Progress',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            'no_show' => 'No Show',
        ];

        return $labels[$this->status] ?? ucfirst($this->status);
    }

    /**
     * Check if the booking is active.
     */
    public function isActive(): bool
    {
        return in_array($this->status, ['pending', 'confirmed', 'in_progress']);
    }

    /**
     * Calculate outstanding balance.
     */
    public function getOutstandingBalanceAttribute(): float
    {
        $totalPaid = $this->payments()->where('status', 'completed')->sum('amount');
        return max(0, $this->total_amount - $totalPaid);
    }

     /**
     * Generate a unique confirmation code
     */
    public function generateConfirmationCode()
    {
        return strtoupper(Str::random(8));
    }

    /**
     * Scope a query to only include bookings with overdue returns.
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', 'in_progress')
                     ->where('dropoff_date', '<', now());
    }
}