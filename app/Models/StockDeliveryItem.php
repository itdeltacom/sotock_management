<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Exception;

class StockDeliveryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_delivery_id',
        'sales_order_item_id',
        'product_id',
        'lot_number',
        'expected_quantity',
        'delivered_quantity',
        'unit_cost',
        'unit_price',
        'notes',
    ];

    protected $casts = [
        'expected_quantity' => 'decimal:3',
        'delivered_quantity' => 'decimal:3',
        'unit_cost' => 'decimal:2',
        'unit_price' => 'decimal:2',
    ];

    // Relationships
    public function stockDelivery()
    {
        return $this->belongsTo(StockDelivery::class);
    }

    public function salesOrderItem()
    {
        return $this->belongsTo(SalesOrderItem::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class, 'reference_id')
            ->where('reference_type', StockMovement::REF_SALES_DELIVERY);
    }

    // Method to process the delivery and update inventory
    public function processDelivery()
    {
        $warehouseId = $this->stockDelivery->warehouse_id;
        $stock = ProductWarehouseStock::where('product_id', $this->product_id)
            ->where('warehouse_id', $warehouseId)
            ->first();
        
        if (!$stock || $stock->available_quantity < $this->delivered_quantity) {
            throw new Exception('Insufficient stock available for delivery.');
        }
        
        // Find appropriate packages to deliver from (FIFO)
        $packagesToUse = StockPackage::where('product_id', $this->product_id)
            ->where('warehouse_id', $warehouseId)
            ->where('available', true)
            ->where('quantity', '>', 0)
            ->orderBy('created_at', 'asc') // First-In-First-Out
            ->get();
        
        $remainingQuantity = $this->delivered_quantity;
        $totalCost = 0;
        
        foreach ($packagesToUse as $package) {
            if ($remainingQuantity <= 0) {
                break;
            }
            
            $quantityFromPackage = min($package->quantity, $remainingQuantity);
            $remainingQuantity -= $quantityFromPackage;
            $totalCost += $quantityFromPackage * $package->cost;
            
            // Update package quantity
            $package->reduceQuantity($quantityFromPackage);
            
            // Record stock movement
            StockMovement::recordMovement(
                $this->product_id,
                $warehouseId,
                StockMovement::REF_SALES_DELIVERY,
                $this->id,
                StockMovement::MOVEMENT_OUT,
                $quantityFromPackage,
                $package->cost,
                $package->id,
                'Stock delivery for SO: ' . ($this->stockDelivery->sales_order_id ?? 'Direct')
            );
        }
        
        // Update the unit cost with CMUP at the time of delivery
        $this->unit_cost = $stock->cmup;
        $this->save();
        
        return true;
    }

    // Method to recalculate expected quantity based on SO
    public function updateExpectedQuantity()
    {
        if ($this->sales_order_item_id) {
            $soItem = $this->salesOrderItem;
            $alreadyDelivered = $soItem->getTotalDelivered() - $this->delivered_quantity;
            $this->expected_quantity = $soItem->quantity - $alreadyDelivered;
            $this->save();
        }
        
        return $this->expected_quantity;
    }
}