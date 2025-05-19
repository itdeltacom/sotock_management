<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'warehouse_id',
        'lot_number',
        'serial_number',
        'production_date',
        'expiry_date',
        'quantity',
        'cost',
        'available',
        'notes',
        'stock_reception_item_id',
    ];

    protected $casts = [
        'production_date' => 'date',
        'expiry_date' => 'date',
        'available' => 'boolean',
    ];

    // Relationships
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function receptionItem()
    {
        return $this->belongsTo(StockReceptionItem::class, 'stock_reception_item_id');
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function transferItems()
    {
        return $this->hasMany(StockTransferItem::class, 'source_package_id');
    }

    public function adjustmentItems()
    {
        return $this->hasMany(StockAdjustmentItem::class);
    }

    // Helper methods
    public function getTotalValue()
    {
        return $this->quantity * $this->cost;
    }

    public function isExpired()
    {
        if (!$this->expiry_date) {
            return false;
        }
        
        return $this->expiry_date->isPast();
    }

    public function isExpiringSoon($days = 30)
    {
        if (!$this->expiry_date) {
            return false;
        }
        
        return now()->diffInDays($this->expiry_date) <= $days;
    }

    // Method to reduce quantity from this package
    public function reduceQuantity($quantity)
    {
        if ($quantity > $this->quantity) {
            throw new \Exception('Insufficient quantity in package.');
        }
        
        $this->quantity -= $quantity;
        
        // If quantity becomes zero, mark as unavailable
        if ($this->quantity <= 0) {
            $this->available = false;
        }
        
        $this->save();
        
        return $this->quantity;
    }
}