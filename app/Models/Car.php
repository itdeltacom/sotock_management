<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Car extends Model
{
    use HasFactory;

    // Constants for transmission types
    const TRANSMISSION_AUTOMATIC = 'automatic';
    const TRANSMISSION_MANUAL = 'manual';
    const TRANSMISSION_SEMI_AUTOMATIC = 'semi-automatic';
    
    // Constants for fuel types
    const FUEL_PETROL = 'petrol';
    const FUEL_DIESEL = 'diesel';
    const FUEL_HYBRID = 'hybrid';
    const FUEL_ELECTRIC = 'electric';
    const FUEL_LPG = 'lpg';

    protected $fillable = [
        'name',
        'slug',
        'category_id',
        'brand_id',
        'price_per_day',
        'discount_percentage',
        'seats',
        'transmission',
        'fuel_type', 
        'mileage',
        'engine_capacity',
        'features',
        'is_available',
        'rating',
        'review_count',
        'main_image',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'description'
    ];

    protected $casts = [
        'features' => 'array',
        'is_available' => 'boolean',
        'price_per_day' => 'float',
        'discount_percentage' => 'float',
        'rating' => 'float',
        'review_count' => 'integer',
        'seats' => 'integer',
        'mileage' => 'integer',
    ];

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
}