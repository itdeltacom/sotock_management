<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class CarDocuments extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'car_id',
        'carte_grise_number',
        'carte_grise_expiry_date',
        'assurance_number',
        'assurance_company',
        'assurance_expiry_date',
        'visite_technique_date',
        'visite_technique_expiry_date',
        'vignette_expiry_date',
        'file_carte_grise',
        'file_assurance',
        'file_visite_technique',
        'file_vignette',
    ];
    
    protected $casts = [
        'carte_grise_expiry_date' => 'date',
        'assurance_expiry_date' => 'date',
        'visite_technique_date' => 'date',
        'visite_technique_expiry_date' => 'date',
        'vignette_expiry_date' => 'date',
    ];
    
    // Relationships
    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class);
    }
    
    // Check if any document is about to expire (within the next 30 days)
    public function hasExpiringDocuments(): bool
    {
        $thirtyDaysFromNow = now()->addDays(30);
        
        return $this->carte_grise_expiry_date && $this->carte_grise_expiry_date <= $thirtyDaysFromNow
            || $this->assurance_expiry_date && $this->assurance_expiry_date <= $thirtyDaysFromNow
            || $this->visite_technique_expiry_date && $this->visite_technique_expiry_date <= $thirtyDaysFromNow
            || $this->vignette_expiry_date && $this->vignette_expiry_date <= $thirtyDaysFromNow;
    }
    
    // Get list of documents that will expire within the next 30 days
    public function getExpiringDocuments(): array
    {
        $thirtyDaysFromNow = now()->addDays(30);
        $expiringDocuments = [];
        
        if ($this->carte_grise_expiry_date && $this->carte_grise_expiry_date <= $thirtyDaysFromNow) {
            $expiringDocuments[] = [
                'document' => 'Carte Grise',
                'expiry_date' => $this->carte_grise_expiry_date,
                'days_left' => now()->diffInDays($this->carte_grise_expiry_date, false)
            ];
        }
        
        if ($this->assurance_expiry_date && $this->assurance_expiry_date <= $thirtyDaysFromNow) {
            $expiringDocuments[] = [
                'document' => 'Assurance',
                'expiry_date' => $this->assurance_expiry_date,
                'days_left' => now()->diffInDays($this->assurance_expiry_date, false)
            ];
        }
        
        if ($this->visite_technique_expiry_date && $this->visite_technique_expiry_date <= $thirtyDaysFromNow) {
            $expiringDocuments[] = [
                'document' => 'Visite Technique',
                'expiry_date' => $this->visite_technique_expiry_date,
                'days_left' => now()->diffInDays($this->visite_technique_expiry_date, false)
            ];
        }
        
        if ($this->vignette_expiry_date && $this->vignette_expiry_date <= $thirtyDaysFromNow) {
            $expiringDocuments[] = [
                'document' => 'Vignette',
                'expiry_date' => $this->vignette_expiry_date,
                'days_left' => now()->diffInDays($this->vignette_expiry_date, false)
            ];
        }
        
        return $expiringDocuments;
    }
}