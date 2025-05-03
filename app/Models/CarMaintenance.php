<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CarMaintenance extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'car_id',
        'maintenance_type',
        'date_performed',
        'mileage_at_service',
        'next_due_date',
        'next_due_mileage',
        'cost',
        'performed_by',
        'notes',
        'oil_type',
        'oil_quantity',
        'parts_replaced'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'date_performed' => 'date',
        'next_due_date' => 'date',
        'cost' => 'decimal:2',
        'mileage_at_service' => 'integer',
        'next_due_mileage' => 'integer',
    ];
    
    /**
     * Get the car that owns the maintenance record.
     */
    public function car()
    {
        return $this->belongsTo(Car::class);
    }
    
    /**
     * Check if maintenance is due soon (within 15 days or 500 km)
     * 
     * @return bool
     */
    public function isDueSoon()
    {
        $isDueSoonByDate = false;
        $isDueSoonByMileage = false;
        
        // Check if due soon by date
        if ($this->next_due_date) {
            $daysUntilDue = now()->diffInDays($this->next_due_date, false);
            $isDueSoonByDate = $daysUntilDue >= 0 && $daysUntilDue <= 15;
        }
        
        // Check if due soon by mileage
        if ($this->next_due_mileage && $this->car) {
            $kmUntilDue = $this->next_due_mileage - $this->car->mileage;
            $isDueSoonByMileage = $kmUntilDue >= 0 && $kmUntilDue <= 500;
        }
        
        return $isDueSoonByDate || $isDueSoonByMileage;
    }
    
    /**
     * Check if maintenance is overdue
     * 
     * @return bool
     */
    public function isOverdue()
    {
        $isOverdueByDate = false;
        $isOverdueByMileage = false;
        
        // Check if overdue by date
        if ($this->next_due_date) {
            $isOverdueByDate = $this->next_due_date->isPast();
        }
        
        // Check if overdue by mileage
        if ($this->next_due_mileage && $this->car) {
            $isOverdueByMileage = $this->next_due_mileage <= $this->car->mileage;
        }
        
        return $isOverdueByDate || $isOverdueByMileage;
    }
    
    /**
     * Get days until next due date
     * 
     * @return int|null
     */
    public function getDaysUntilDueAttribute()
    {
        if (!$this->next_due_date) {
            return null;
        }
        
        return now()->diffInDays($this->next_due_date, false);
    }
    
    /**
     * Get kilometers until next due mileage
     * 
     * @return int|null
     */
    public function getKmUntilDueAttribute()
    {
        if (!$this->next_due_mileage || !$this->car) {
            return null;
        }
        
        return $this->next_due_mileage - $this->car->mileage;
    }
    
    /**
     * Get maintenance status
     * 
     * @return string
     */
    public function getStatusAttribute()
    {
        if ($this->isOverdue()) {
            return 'overdue';
        }
        
        if ($this->isDueSoon()) {
            return 'due_soon';
        }
        
        return 'ok';
    }
    
    /**
     * Get formatted maintenance type
     * 
     * @return string
     */
    public function getMaintenanceTitleAttribute()
    {
        return ucfirst(str_replace('_', ' ', $this->maintenance_type));
    }
    
    /**
     * Calculate next due date based on maintenance type and date performed
     * 
     * @param string $maintenanceType
     * @param \Carbon\Carbon $datePerformed
     * @return \Carbon\Carbon|null
     */
    public static function calculateNextDueDate($maintenanceType, $datePerformed)
    {
        // Define maintenance intervals in months
        $intervals = [
            'oil_change' => 6,
            'tire_rotation' => 6,
            'brake_service' => 12,
            'air_filter' => 12,
            'cabin_filter' => 12,
            'timing_belt' => 36,
            'spark_plugs' => 24,
            'battery_replacement' => 24,
            'transmission_service' => 24,
            'wheel_alignment' => 12,
            'fluid_flush' => 18,
            'general_service' => 12,
        ];
        
        if (!isset($intervals[$maintenanceType])) {
            return null;
        }
        
        $datePerformed = $datePerformed instanceof Carbon ? $datePerformed : Carbon::parse($datePerformed);
        
        return $datePerformed->copy()->addMonths($intervals[$maintenanceType]);
    }
    
    /**
     * Calculate next due mileage based on maintenance type and current mileage
     * 
     * @param string $maintenanceType
     * @param int $currentMileage
     * @return int|null
     */
    public static function calculateNextDueMileage($maintenanceType, $currentMileage)
    {
        // Define maintenance intervals in kilometers
        $intervals = [
            'oil_change' => 10000,
            'tire_rotation' => 8000,
            'brake_service' => 20000,
            'air_filter' => 15000,
            'cabin_filter' => 15000,
            'timing_belt' => 80000,
            'spark_plugs' => 40000,
            'battery_replacement' => 50000,
            'transmission_service' => 60000,
            'wheel_alignment' => 20000,
            'fluid_flush' => 30000,
            'general_service' => 15000,
        ];
        
        if (!isset($intervals[$maintenanceType])) {
            return null;
        }
        
        return $currentMileage + $intervals[$maintenanceType];
    }
    
    /**
     * Get all maintenance types with descriptions
     * 
     * @return array
     */
    public static function getMaintenanceTypes()
    {
        return [
            'oil_change' => 'Oil Change',
            'tire_rotation' => 'Tire Rotation',
            'brake_service' => 'Brake Service',
            'air_filter' => 'Air Filter Replacement',
            'cabin_filter' => 'Cabin Filter Replacement',
            'timing_belt' => 'Timing Belt Replacement',
            'spark_plugs' => 'Spark Plugs Replacement',
            'battery_replacement' => 'Battery Replacement',
            'transmission_service' => 'Transmission Service',
            'wheel_alignment' => 'Wheel Alignment',
            'fluid_flush' => 'Fluid Flush',
            'general_service' => 'General Service',
            'engine_repair' => 'Engine Repair',
            'suspension_repair' => 'Suspension Repair',
            'electrical_repair' => 'Electrical Repair',
            'inspection' => 'Inspection',
            'other' => 'Other'
        ];
    }
}