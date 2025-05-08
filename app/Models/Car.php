<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Car extends Model
{
    use HasFactory;

    // Status constants
    const STATUS_AVAILABLE = 'available';
    const STATUS_RENTED = 'rented';
    const STATUS_MAINTENANCE = 'maintenance';
    const STATUS_UNAVAILABLE = 'unavailable';
    const STATUS_RESERVED = 'reserved';
    
    // Transmission constants
    const TRANSMISSION_AUTOMATIC = 'automatic';
    const TRANSMISSION_MANUAL = 'manual';
    
    // Fuel type constants
    const FUEL_DIESEL = 'diesel';
    const FUEL_GASOLINE = 'gasoline';
    const FUEL_HYBRID = 'hybrid';
    const FUEL_ELECTRIC = 'electric';
    const FUEL_LPG = 'lpg';

    protected $fillable = [
        'name',
        'slug',
        'brand_name',
        'brand_id',
        'category_id',
        'model',
        'year',
        'chassis_number',
        'matricule',
        'color',
        'mise_en_service_date',
        'status',
        'is_available',
        'daily_price',
        'price_per_day',
        'weekly_price',
        'monthly_price',
        'discount_percentage',
        'fuel_type',
        'transmission',
        'mileage',
        'current_mileage', // Added new field
        'engine_capacity',
        'seats',
        'features',
        'description',
        'rating',
        'review_count',
        'main_image',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'insurance_number',
        'vignette_date',
        'technical_inspection_date',
        'grey_card_number'
    ];

    protected $casts = [
        'features' => 'array',
        'is_available' => 'boolean',
        'daily_price' => 'decimal:2',
        'price_per_day' => 'decimal:2',
        'weekly_price' => 'decimal:2',
        'monthly_price' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'rating' => 'decimal:1',
        'review_count' => 'integer',
        'seats' => 'integer',
        'mileage' => 'integer',
        'current_mileage' => 'integer',
        'mise_en_service_date' => 'date',
        'vignette_date' => 'date',
        'technical_inspection_date' => 'date'
    ];

    // Relationships
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(CarImage::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }
    
    public function documents(): HasOne
    {
        return $this->hasOne(CarDocuments::class);
    }

    // Accessors
    public function getFormattedPriceAttribute(): string
    {
        return number_format($this->daily_price, 2) . ' MAD';
    }

    public function getCurrentPriceAttribute(): float
    {
        return $this->daily_price * (1 - ($this->discount_percentage / 100));
    }

    public function getFeaturedImageUrlAttribute(): ?string
    {
        return $this->featured_image ? asset('storage/' . $this->featured_image) : null;
    }
    
    /**
     * Get the formatted mileage with units
     */
    public function getFormattedMileageAttribute(): string
    {
        return number_format($this->mileage) . ' km';
    }
    
    /**
     * Get the active bookings for this car
     */
    public function activeBookings(): HasMany
    {
        return $this->bookings()
            ->whereIn('status', ['pending', 'confirmed', 'in_progress']);
    }
    
    /**
     * Check if car is currently booked
     */
    public function isBooked(): bool
    {
        return $this->activeBookings()->exists();
    }
    
    /**
     * Get the current booking if car is in use
     */
    public function currentBooking()
    {
        return $this->bookings()
            ->where('status', 'in_progress')
            ->latest()
            ->first();
    }
    
    /**
     * Get all maintenance records for the car.
     */
    public function maintenance(): HasMany
    {
        return $this->hasMany(CarMaintenance::class);
    }

    /**
     * Get the most recent maintenance record.
     */
    public function lastMaintenance(): HasOne
    {
        return $this->hasOne(CarMaintenance::class)->latest('date_performed');
    }

    /**
     * Get all maintenance records that are due soon or overdue.
     */
    public function dueMaintenance()
    {
        return $this->maintenance()
            ->where(function($query) {
                $query->whereDate('next_due_date', '<=', now()->addDays(15))
                    ->orWhereRaw('next_due_mileage - ? <= 500', [$this->mileage]);
            });
    }

    /**
     * Get all overdue maintenance records.
     */
    public function overdueMaintenance()
    {
        return $this->maintenance()
            ->where(function($query) {
                $query->whereDate('next_due_date', '<', now())
                    ->orWhereRaw('next_due_mileage <= ?', [$this->mileage]);
            });
    }

    /**
     * Check if car has any maintenance due soon or overdue.
     * 
     * @return bool
     */
    public function hasMaintenanceDue(): bool
    {
        return $this->dueMaintenance()->count() > 0;
    }

    /**
     * Check if the car is in need of maintenance based on time or mileage.
     * 
     * @return bool
     */
    public function needsMaintenance(): bool
    {
        // Check if any maintenance is overdue
        if ($this->overdueMaintenance()->count() > 0) {
            return true;
        }
        
        // Check when was the last oil change
        $lastOilChange = $this->maintenance()
            ->where('maintenance_type', 'oil_change')
            ->latest('date_performed')
            ->first();
            
        if ($lastOilChange) {
            // If last oil change was more than 6 months ago or 10,000 km
            if ($lastOilChange->date_performed->addMonths(6)->isPast() ||
                ($this->mileage - $lastOilChange->mileage_at_service) >= 10000) {
                return true;
            }
        } else {
            // No oil change record found, assume maintenance needed
            return true;
        }
        
        // Check for general service
        $lastGeneralService = $this->maintenance()
            ->where('maintenance_type', 'general_service')
            ->latest('date_performed')
            ->first();
            
        if ($lastGeneralService) {
            // If last general service was more than 12 months ago or 15,000 km
            if ($lastGeneralService->date_performed->addMonths(12)->isPast() ||
                ($this->mileage - $lastGeneralService->mileage_at_service) >= 15000) {
                return true;
            }
        } else {
            // No general service record found, assume maintenance needed
            return true;
        }
        
        return false;
    }

    /**
     * Get the next scheduled maintenance date.
     * 
     * @return \Carbon\Carbon|null
     */
    public function getNextMaintenanceDateAttribute()
    {
        $nextMaintenance = $this->maintenance()
            ->whereNotNull('next_due_date')
            ->orderBy('next_due_date')
            ->first();
            
        return $nextMaintenance ? $nextMaintenance->next_due_date : null;
    }

    /**
     * Get the next scheduled maintenance mileage.
     * 
     * @return int|null
     */
    public function getNextMaintenanceMileageAttribute()
    {
        $nextMaintenance = $this->maintenance()
            ->whereNotNull('next_due_mileage')
            ->orderBy('next_due_mileage')
            ->first();
            
        return $nextMaintenance ? $nextMaintenance->next_due_mileage : null;
    }

    /**
     * Get maintenance status label.
     * 
     * @return string
     */
    public function getMaintenanceStatusAttribute(): string
    {
        if ($this->overdueMaintenance()->count() > 0) {
            return 'overdue';
        }
        
        if ($this->dueMaintenance()->count() > 0) {
            return 'due_soon';
        }
        
        return 'ok';
    }

    /**
     * Total maintenance cost.
     * 
     * @return float
     */
    public function getTotalMaintenanceCostAttribute(): float
    {
        return $this->maintenance()->sum('cost');
    }
    
    /**
     * Get availability status for a specific date range
     * 
     * @param string $startDate
     * @param string $endDate
     * @return bool
     */
    public function isAvailableForDates(string $startDate, string $endDate): bool
    {
        if (!$this->is_available || $this->status !== self::STATUS_AVAILABLE) {
            return false;
        }
        
        // Check if there are any overlapping bookings
        return !$this->bookings()
            ->where(function ($query) use ($startDate, $endDate) {
                $query->where(function ($q) use ($startDate, $endDate) {
                    $q->where('pickup_date', '<=', $startDate)
                      ->where('dropoff_date', '>=', $startDate);
                })->orWhere(function ($q) use ($startDate, $endDate) {
                    $q->where('pickup_date', '<=', $endDate)
                      ->where('dropoff_date', '>=', $endDate);
                })->orWhere(function ($q) use ($startDate, $endDate) {
                    $q->where('pickup_date', '>=', $startDate)
                      ->where('dropoff_date', '<=', $endDate);
                });
            })
            ->whereIn('status', ['pending', 'confirmed', 'in_progress'])
            ->exists();
    }
    
    /**
     * Get total revenue generated by this car
     * 
     * @return float
     */
    public function getTotalRevenueAttribute(): float
    {
        return $this->bookings()
            ->whereIn('status', ['completed', 'in_progress'])
            ->where('payment_status', 'paid')
            ->sum('total_amount');
    }
    
    /**
     * Get car utilization percentage (days booked / total days since added)
     * 
     * @return float
     */
    public function getUtilizationPercentageAttribute(): float
    {
        $totalDays = now()->diffInDays($this->created_at) ?: 1;
        
        $bookedDays = $this->bookings()
            ->whereIn('status', ['completed', 'in_progress'])
            ->sum('total_days');
            
        return min(100, round(($bookedDays / $totalDays) * 100, 2));
    }
    
    /**
     * Set the mileage attribute - updates both mileage and current_mileage
     * 
     * @param int $value
     * @return void
     */
    public function setMileageAttribute($value)
    {
        $this->attributes['mileage'] = $value;
        $this->attributes['current_mileage'] = $value;
    }
}