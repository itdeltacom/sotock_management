<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CarMaintenance extends Model
{
    use HasFactory;

    protected $fillable = [
        'car_id',
        'maintenance_type', // 'oil_change', 'tire_change', 'brake_service', 'general_service', etc.
        'date_performed',
        'next_due_date',
        'next_due_mileage',
        'cost',
        'performed_by',
        'notes',
        'mileage_at_service',
        'oil_type',
        'oil_quantity',
        'is_completed',
        'parts_replaced',
    ];

    protected $casts = [
        'date_performed' => 'date',
        'next_due_date' => 'date',
        'cost' => 'decimal:2',
        'mileage_at_service' => 'integer',
        'next_due_mileage' => 'integer',
        'is_completed' => 'boolean',
    ];

    // Relationships
    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class);
    }
    
    // Check if maintenance is due soon (within 500 km or 15 days)
    public function isDueSoon(): bool
    {
        $fifteenDaysFromNow = now()->addDays(15);
        $car = $this->car;
        
        return ($this->next_due_date && $this->next_due_date <= $fifteenDaysFromNow)
            || ($this->next_due_mileage && $car && ($this->next_due_mileage - $car->mileage) <= 500);
    }
    
    // Calculate days or kilometers left until next maintenance
    public function getTimeOrDistanceUntilDue(): array
    {
        $result = [];
        
        if ($this->next_due_date) {
            $result['days_left'] = now()->diffInDays($this->next_due_date, false);
        }
        
        if ($this->next_due_mileage && $this->car) {
            $result['kilometers_left'] = $this->next_due_mileage - $this->car->mileage;
        }
        
        return $result;
    }
}