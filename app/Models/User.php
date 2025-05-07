<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'photo',
        'address',
        'google_id',
        'facebook_id',
        'status',
        'id_number',
        'license_number',
        'notes',
        'emergency_contact_name',
        'emergency_contact_phone',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'google_id',
        'facebook_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'license_expiry_date' => 'date',
    ];

    public function getRouteKeyName()
    {
        return 'id';
    }
    
    /**
     * The accessors to append to the model's array form.
     *
     * @var array<string>
     */
    protected $appends = [
        'credit_score',
    ];

    /**
     * Get all contracts for the user.
     */
    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class, 'client_id');
    }

    /**
     * Get all active contracts for the user.
     */
    public function activeContracts(): HasMany
    {
        return $this->hasMany(Contract::class, 'client_id')
            ->where('status', 'active');
    }

    /**
     * Get all overdue contracts for the user.
     */
    public function overdueContracts(): HasMany
    {
        return $this->hasMany(Contract::class, 'client_id')
            ->where('status', 'active')
            ->where('end_date', '<', now());
    }

    /**
     * Check if user has any overdue contracts.
     */
    public function hasOverdueContracts(): bool
    {
        return $this->contracts()
            ->where('status', 'active')
            ->where('end_date', '<', now())
            ->exists();
    }

    /**
     * Get overdue contracts collection.
     */
    public function getOverdueContracts()
    {
        return $this->contracts()
            ->where('status', 'active')
            ->where('end_date', '<', now())
            ->get();
    }

    /**
     * Get full name attribute.
     */
    public function getFullNameAttribute(): string
    {
        return $this->name;
    }

    /**
     * Get count of active contracts (for dashboard/attributes).
     */
    public function getActiveContractsAttribute(): int
    {
        return $this->contracts()
            ->where('status', 'active')
            ->count();
    }

    /**
     * Get count of overdue contracts (for dashboard/attributes).
     */
    public function getOverdueContractsAttribute(): int
    {
        return $this->contracts()
            ->where('status', 'active')
            ->where('end_date', '<', now())
            ->count();
    }

    /**
     * Get total contracts count.
     */
    public function getTotalContractsAttribute(): int
    {
        return $this->contracts()->count();
    }

    /**
     * Get user's total outstanding balance.
     */
    public function getTotalOutstandingBalanceAttribute(): float
    {
        return $this->contracts()
            ->where('payment_status', '!=', 'paid')
            ->get()
            ->sum(function ($contract) {
                return $contract->total_amount - $contract->payments()->sum('amount');
            });
    }

    /**
     * Get user's photo URL.
     */
    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo ? asset('storage/' . $this->photo) : null;
    }

    /**
     * Get user's credit score.
     */
    public function getCreditScoreAttribute(): int
    {
        return $this->calculateCreditScore();
    }

    /**
     * Check if user can rent (based on business rules).
     */
    public function canRent(): bool
    {
        // Check if user is active
        if ($this->status !== 'active') {
            return false;
        }

        // Check if user has overdue contracts
        if ($this->hasOverdueContracts()) {
            return false;
        }

        // Business rule: Maximum active contracts limit
        $maxActiveContracts = config('rental.max_active_contracts', 3);
        if ($this->activeContracts()->count() >= $maxActiveContracts) {
            return false;
        }

        // Business rule: Maximum outstanding balance limit
        $maxOutstandingBalance = config('rental.max_outstanding_balance', 10000);
        if ($this->total_outstanding_balance > $maxOutstandingBalance) {
            return false;
        }

        // Business rule: Minimum completed contracts for multiple rentals
        $completedContracts = $this->contracts()->where('status', 'completed')->count();
        $activeContracts = $this->activeContracts()->count();
        
        // If user wants to have more than 1 active contract, they need at least 2 completed contracts
        if ($activeContracts >= 1 && $completedContracts < 2) {
            return false;
        }

        // Business rule: Check if user has any unpaid contracts older than 30 days
        $hasOldUnpaidContracts = $this->contracts()
            ->where('payment_status', '!=', 'paid')
            ->where('created_at', '<', now()->subDays(30))
            ->exists();
        
        if ($hasOldUnpaidContracts) {
            return false;
        }

        // Business rule: Check for bad payment history
        $latePaymentsCount = $this->contracts()
            ->whereHas('payments', function ($query) {
                $query->whereColumn('payment_date', '>', 'contracts.due_date');
            })
            ->count();
        
        // If user has more than 3 late payments, they can't rent
        if ($latePaymentsCount > 3) {
            return false;
        }

        // Business rule: Check if license is expired
        if ($this->license_expiry_date && $this->license_expiry_date < now()) {
            return false;
        }

        return true;
    }

    /**
     * Get reason why user cannot rent.
     */
    public function getRentalRestrictionReason(): ?string
    {
        if ($this->status !== 'active') {
            return 'Account is ' . $this->status;
        }

        if ($this->hasOverdueContracts()) {
            $overdueCount = $this->overdueContracts()->count();
            return "You have {$overdueCount} overdue contract(s)";
        }

        $maxActiveContracts = config('rental.max_active_contracts', 3);
        if ($this->activeContracts()->count() >= $maxActiveContracts) {
            return "Maximum active contracts limit ({$maxActiveContracts}) reached";
        }

        $maxOutstandingBalance = config('rental.max_outstanding_balance', 10000);
        if ($this->total_outstanding_balance > $maxOutstandingBalance) {
            return "Outstanding balance exceeds limit (" . number_format($maxOutstandingBalance, 2) . " MAD)";
        }

        $completedContracts = $this->contracts()->where('status', 'completed')->count();
        $activeContracts = $this->activeContracts()->count();
        
        if ($activeContracts >= 1 && $completedContracts < 2) {
            return "You need at least 2 completed contracts to have multiple active rentals";
        }

        $hasOldUnpaidContracts = $this->contracts()
            ->where('payment_status', '!=', 'paid')
            ->where('created_at', '<', now()->subDays(30))
            ->exists();
        
        if ($hasOldUnpaidContracts) {
            return "You have unpaid contracts older than 30 days";
        }

        $latePaymentsCount = $this->contracts()
            ->whereHas('payments', function ($query) {
                $query->whereColumn('payment_date', '>', 'contracts.due_date');
            })
            ->count();
        
        if ($latePaymentsCount > 3) {
            return "Too many late payments in history";
        }

        if ($this->license_expiry_date && $this->license_expiry_date < now()) {
            return "Your driver's license has expired";
        }

        return null;
    }

    /**
     * Get user's rental eligibility status with details.
     */
    public function getRentalEligibilityStatus(): array
    {
        $canRent = $this->canRent();
        $reason = $this->getRentalRestrictionReason();
        
        return [
            'can_rent' => $canRent,
            'reason' => $reason,
            'active_contracts' => $this->activeContracts()->count(),
            'max_active_contracts' => config('rental.max_active_contracts', 3),
            'outstanding_balance' => $this->total_outstanding_balance,
            'max_outstanding_balance' => config('rental.max_outstanding_balance', 10000),
            'overdue_contracts' => $this->overdueContracts()->count(),
            'completed_contracts' => $this->contracts()->where('status', 'completed')->count(),
            'credit_score' => $this->credit_score,
        ];
    }

    /**
     * Calculate user's credit score based on rental history.
     */
    public function calculateCreditScore(): int
    {
        $score = 100; // Start with perfect score
        
        // Deduct points for overdue contracts
        $overdueContracts = $this->overdueContracts()->count();
        $score -= $overdueContracts * 10;
        
        // Deduct points for late payments
        $latePayments = $this->contracts()
            ->whereHas('payments', function ($query) {
                $query->whereColumn('payment_date', '>', 'contracts.due_date');
            })
            ->count();
        $score -= $latePayments * 5;
        
        // Deduct points for cancelled contracts
        $cancelledContracts = $this->contracts()->where('status', 'cancelled')->count();
        $score -= $cancelledContracts * 5;
        
        // Add points for completed contracts
        $completedContracts = $this->contracts()->where('status', 'completed')->count();
        $score += min($completedContracts * 2, 20); // Max 20 bonus points
        
        // Add points for on-time payments
        $onTimePayments = $this->contracts()
            ->whereHas('payments', function ($query) {
                $query->whereColumn('payment_date', '<=', 'contracts.due_date');
            })
            ->count();
        $score += min($onTimePayments * 1, 10); // Max 10 bonus points
        
        // Ensure score is between 0 and 100
        return max(0, min(100, $score));
    }

    /**
     * Get user's rental history statistics.
     */
    public function getRentalStatistics(): array
    {
        return [
            'total_contracts' => $this->contracts()->count(),
            'active_contracts' => $this->activeContracts()->count(),
            'completed_contracts' => $this->contracts()->where('status', 'completed')->count(),
            'cancelled_contracts' => $this->contracts()->where('status', 'cancelled')->count(),
            'total_spent' => $this->contracts()->sum('total_amount'),
            'total_paid' => $this->contracts()->with('payments')->get()->sum(function ($contract) {
                return $contract->payments->sum('amount');
            }),
            'outstanding_balance' => $this->total_outstanding_balance,
            'average_rental_duration' => $this->contracts()->avg('duration_in_days'),
        ];
    }
}