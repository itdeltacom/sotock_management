<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Car extends Model
{
    use HasFactory;

    // Status constants
    const STATUS_AVAILABLE = 'available';
    const STATUS_RENTED = 'rented';
    const STATUS_MAINTENANCE = 'maintenance';
    const STATUS_UNAVAILABLE = 'unavailable';
    
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

    // Accessors
    public function getFormattedPriceAttribute(): string
    {
        return number_format($this->daily_price, 2) . ' MAD';
    }

    public function getCurrentPriceAttribute(): float
    {
        return $this->daily_price * (1 - ($this->discount_percentage / 100));
    }

    public function documents()
{
    return $this->hasOne(CarDocuments::class);
}
}