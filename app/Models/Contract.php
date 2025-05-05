<?php

namespace App\Models;

use App\Models\User;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Contract extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'car_id',
        'start_date',
        'end_date',
        'rental_fee',
        'deposit_amount',
        'status', // 'active', 'completed', 'cancelled'
        'payment_status', // 'pending', 'partial', 'paid'
        'notes',
        'start_mileage',
        'end_mileage',
        'extension_days',
        'total_amount',
        'discount',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'rental_fee' => 'decimal:2',
        'deposit_amount' => 'decimal:2',
        'start_mileage' => 'integer',
        'end_mileage' => 'integer',
        'extension_days' => 'integer',
        'total_amount' => 'decimal:2',
        'discount' => 'decimal:2',
    ];

    // Relationships
    public function client(): BelongsTo
{
    return $this->belongsTo(User::class, 'client_id');
}
    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class);
    }
    
    // Calculate the rental duration in days
    public function getDurationInDaysAttribute(): int
    {
        return $this->start_date->diffInDays($this->end_date) + 1;
    }
    
    // Check if contract is ending soon (within next 2 days)
    public function isEndingSoon(): bool
    {
        return $this->status === 'active' && $this->end_date->diffInDays(now()) <= 2;
    }
    
    // Check if contract is overdue
    public function isOverdue(): bool
    {
        return $this->status === 'active' && $this->end_date < now();
    }
    
    // Calculate overdue days
    public function getOverdueDaysAttribute(): int
    {
        if (!$this->isOverdue()) {
            return 0;
        }
        
        return now()->diffInDays($this->end_date);
    }
    
    // Calculate estimated penalties based on overdue days
    public function getEstimatedPenaltyAttribute(): float
    {
        if (!$this->isOverdue()) {
            return 0;
        }
        
        // Assuming penalty is 150% of daily rate
        return $this->getOverdueDaysAttribute() * ($this->rental_fee * 1.5);
    }
    public function payments()
{
    return $this->hasMany(Payment::class);
}

public function getTotalPaidAttribute()
{
    return $this->payments()->sum('amount');
}

public function getOutstandingBalanceAttribute()
{
    return $this->total_amount - $this->total_paid;
}
public function contracts()
{
    return $this->hasMany(Contract::class, 'client_id');
}
}