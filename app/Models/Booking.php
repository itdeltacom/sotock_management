<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    use HasFactory;

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
        'transaction_id'
    ];

    protected $casts = [
        'pickup_date' => 'date',
        'dropoff_date' => 'date',
        'total_days' => 'integer',
        'base_price' => 'float',
        'discount_amount' => 'float',
        'tax_amount' => 'float',
        'total_amount' => 'float',
    ];

    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}