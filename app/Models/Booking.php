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
        'confirmation_code',
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
        'language_preference', // Customer's preferred language (Arabic, French, etc.)
        'gps_enabled', // GPS navigation option
        'child_seat', // Child seat option
        'completed_at', // Date/time when booking is completed
        'start_mileage', // Starting mileage when car is picked up
        'end_mileage', // Ending mileage when car is returned
        'extra_mileage', // Extra mileage beyond the limit
        'extra_mileage_charges', // Charges for extra mileage
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
        'start_mileage' => 'integer',
        'end_mileage' => 'integer',
        'extra_mileage' => 'integer',
        'extra_mileage_charges' => 'float',
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
        'total_mileage',
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
     * Calculate total mileage driven during rental period.
     */
    public function getTotalMileageAttribute(): int
    {
        if (!$this->end_mileage || !$this->start_mileage) {
            return 0;
        }
        
        return max(0, $this->end_mileage - $this->start_mileage);
    }

    /**
     * Check if the booking is active.
     */
    public function isActive(): bool
    {
        return in_array($this->status, ['pending', 'confirmed', 'in_progress']);
    }

    /**
     * Calculate extra mileage charges when booking is completed.
     */
    public function calculateExtraMileageCharges(): float
    {
        if (!$this->end_mileage || !$this->start_mileage || !$this->mileage_limit) {
            return 0;
        }
        
        $totalMileage = $this->end_mileage - $this->start_mileage;
        $allowedMileage = $this->mileage_limit * $this->total_days;
        
        if ($totalMileage <= $allowedMileage) {
            return 0;
        }
        
        $extraMileage = $totalMileage - $allowedMileage;
        $extraMileageCharges = $extraMileage * $this->extra_mileage_cost;
        
        $this->extra_mileage = $extraMileage;
        $this->extra_mileage_charges = $extraMileageCharges;
        $this->save();
        
        return $extraMileageCharges;
    }

    /**
     * Calculate outstanding balance.
     */
    public function getOutstandingBalanceAttribute(): float
    {
        $totalPaid = $this->payments()->where('status', 'completed')->sum('amount');
        $totalCharges = $this->total_amount + ($this->extra_mileage_charges ?? 0);
        
        return max(0, $totalCharges - $totalPaid);
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
    
    /**
     * Complete the booking with end mileage and update car status
     */
    public function completeBooking(int $endMileage): void
    {
        $this->end_mileage = $endMileage;
        $this->status = 'completed';
        $this->completed_at = now();
        
        // Calculate any extra mileage charges
        $this->calculateExtraMileageCharges();
        
        $this->save();
        
        // Update car's mileage
        if ($this->car) {
            $this->car->mileage = $endMileage;
            $this->car->status = 'available';
            $this->car->is_available = true;
            $this->car->save();
        }
    }
    
    /**
     * Start the rental by updating the booking and car status
     */
    public function startRental(int $confirmedStartMileage = null): void
    {
        // Update booking status
        $this->status = 'in_progress';
        
        // Update start mileage if provided
        if ($confirmedStartMileage !== null) {
            $this->start_mileage = $confirmedStartMileage;
        }
        
        $this->save();
        
        // Update car status
        if ($this->car) {
            $this->car->status = 'rented';
            $this->car->is_available = false;
            $this->car->save();
        }
    }
}