<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductWarehouseStock extends Model
{
    use HasFactory;

    protected $table = 'product_warehouse_stock';

    protected $fillable = [
        'product_id',
        'warehouse_id',
        'available_quantity',
        'reserved_quantity',
        'cmup',
        'min_stock',
        'max_stock',
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

    // Helper methods
    public function getTotalQuantity()
    {
        return $this->available_quantity + $this->reserved_quantity;
    }

    public function isLowStock()
    {
        if ($this->min_stock === null) {
            return false;
        }
        
        return $this->available_quantity < $this->min_stock;
    }

    public function isOverStock()
    {
        if ($this->max_stock === null) {
            return false;
        }
        
        return $this->available_quantity > $this->max_stock;
    }

    // Method to update CMUP (Coût Moyen Unitaire Pondéré)
    public function updateCMUP($newQuantity, $newUnitCost)
    {
        $currentQuantity = $this->available_quantity;
        $currentTotalValue = $currentQuantity * $this->cmup;
        
        $newTotalValue = $newUnitCost * $newQuantity;
        $totalQuantity = $currentQuantity + $newQuantity;
        
        // Calculate new CMUP
        if ($totalQuantity > 0) {
            $this->cmup = ($currentTotalValue + $newTotalValue) / $totalQuantity;
        } else {
            $this->cmup = $newUnitCost; // If no stock, just use the new unit cost
        }
        
        $this->available_quantity = $totalQuantity;
        $this->save();
        
        return $this->cmup;
    }

    // Method to reduce stock without affecting CMUP
    public function reduceStock($quantity)
    {
        if ($quantity > $this->available_quantity) {
            throw new \Exception('Insufficient stock available.');
        }
        
        $this->available_quantity -= $quantity;
        $this->save();
        
        return $this->available_quantity;
    }

    // Method to reserve stock for sales orders
    public function reserveStock($quantity)
    {
        if ($quantity > $this->available_quantity) {
            throw new \Exception('Insufficient stock available to reserve.');
        }
        
        $this->available_quantity -= $quantity;
        $this->reserved_quantity += $quantity;
        $this->save();
        
        return $this->reserved_quantity;
    }

    // Method to release reserved stock
    public function releaseReservedStock($quantity)
    {
        if ($quantity > $this->reserved_quantity) {
            throw new \Exception('Trying to release more than reserved quantity.');
        }
        
        $this->reserved_quantity -= $quantity;
        $this->available_quantity += $quantity;
        $this->save();
        
        return $this->available_quantity;
    }
}