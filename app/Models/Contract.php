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
    
    public function booking()
{
    return $this->belongsTo(Booking::class);
}
    public function payments()
    {
        return $this->hasMany(Payment::class);
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
    
    // Get total paid amount
    public function getTotalPaidAttribute()
    {
        return $this->payments()->sum('amount');
    }
    
    // Get outstanding balance
    public function getOutstandingBalanceAttribute()
    {
        return $this->total_amount - $this->total_paid;
    }
    
    /**
     * Add a payment to this contract
     *
     * @param array $data
     * @return \App\Models\Payment
     */
    public function addPayment(array $data)
    {
        // Create the payment
        $payment = $this->payments()->create([
            'amount' => $data['amount'],
            'payment_method' => $data['payment_method'],
            'payment_date' => $data['payment_date'],
            'reference' => $data['reference'] ?? null,
            'notes' => $data['notes'] ?? null,
            'processed_by' => $data['processed_by']
        ]);
        
        // Update payment status based on total paid
        $this->recalculatePaymentStatus();
        
        return $payment;
    }
    
    /**
     * Recalculate and update the contract's payment status
     */
    public function recalculatePaymentStatus()
    {
        $totalPaid = $this->total_paid;
        
        if ($totalPaid >= $this->total_amount) {
            $this->payment_status = 'paid';
        } elseif ($totalPaid > 0) {
            $this->payment_status = 'partial';
        } else {
            $this->payment_status = 'pending';
        }
        
        $this->save();
        
        return $this;
    }
    
    /**
     * Get the payment progress percentage
     *
     * @return int
     */
    public function getPaymentProgressAttribute()
    {
        if ($this->total_amount <= 0) {
            return 100;
        }
        
        return min(100, round(($this->total_paid / $this->total_amount) * 100));
    }
    
    /**
     * Get status color for badges
     *
     * @return string
     */
    public function getStatusColorAttribute()
    {
        $colors = [
            'active' => 'success',
            'completed' => 'info',
            'cancelled' => 'danger'
        ];
        
        return $colors[$this->status] ?? 'secondary';
    }
    
    /**
     * Get payment status color for badges
     *
     * @return string
     */
    public function getPaymentStatusColorAttribute()
    {
        $colors = [
            'paid' => 'success',
            'partial' => 'warning',
            'pending' => 'danger'
        ];
        
        return $colors[$this->payment_status] ?? 'secondary';
    }
    
    /**
     * Check if contract can be edited
     *
     * @return bool
     */
    public function canBeEdited()
    {
        return $this->status === 'active';
    }
    
    /**
     * Check if contract can be completed
     *
     * @return bool
     */
    public function canBeCompleted()
    {
        return $this->status === 'active';
    }
    
    /**
     * Check if contract can be cancelled
     *
     * @return bool
     */
    public function canBeCancelled()
    {
        return $this->status === 'active';
    }
    
    /**
     * Check if contract can be extended
     *
     * @return bool
     */
    public function canBeExtended()
    {
        return $this->status === 'active';
    }
    
    /**
     * Complete a contract
     *
     * @param int $endMileage
     * @return bool
     */
    public function complete($endMileage)
    {
        $this->status = 'completed';
        $this->end_mileage = $endMileage;
        $this->save();
        
        // Update car status to available
        $this->car()->update(['status' => 'available']);
        
        return true;
    }
    
    /**
     * Cancel a contract
     *
     * @return bool
     */
    public function cancel()
    {
        $this->status = 'cancelled';
        $this->save();
        
        // Update car status to available
        $this->car()->update(['status' => 'available']);
        
        return true;
    }
    
    /**
     * Extend a contract
     *
     * @param int $days
     * @param float|null $newRentalFee
     * @return bool
     */
    public function extend($days, $newRentalFee = null)
    {
        // Store the original end date
        $originalEndDate = $this->end_date->copy();
        
        // Update end date
        $this->end_date = $this->end_date->addDays($days);
        
        // Update rental fee if provided
        if ($newRentalFee !== null) {
            $this->rental_fee = $newRentalFee;
        }
        
        // Calculate additional amount
        $additionalAmount = $this->rental_fee * $days;
        
        // Update total amount
        $this->total_amount += $additionalAmount;
        
        // Track extension days
        $this->extension_days = ($this->extension_days ?? 0) + $days;
        
        $this->save();
        
        return true;
    }
    
    /**
     * Scope for contracts ending soon
     */
    public function scopeEndingSoon($query)
    {
        return $query->where('status', 'active')
            ->whereDate('end_date', '>=', now())
            ->whereDate('end_date', '<=', now()->addDays(2));
    }
    
    /**
     * Scope for overdue contracts
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', 'active')
            ->whereDate('end_date', '<', now());
    }
    
    /**
     * Scope for unpaid contracts
     */
    public function scopeUnpaid($query)
    {
        return $query->whereIn('payment_status', ['pending', 'partial']);
    }
}