<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'warehouse_id',
        'stock_package_id',
        'reference_type',
        'reference_id',
        'movement_type',
        'quantity',
        'unit_cost',
        'total_cost',
        'cmup_before',
        'cmup_after',
        'stock_before',
        'stock_after',
        'created_by',
        'notes',
    ];

    // Define movement types constants
    const MOVEMENT_IN = 'in';
    const MOVEMENT_OUT = 'out';

    // Define reference types constants
    const REF_PURCHASE_RECEPTION = 'purchase_reception';
    const REF_SALES_DELIVERY = 'sales_delivery';
    const REF_TRANSFER = 'transfer';
    const REF_ADJUSTMENT = 'adjustment';

    // Relationships
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function stockPackage()
    {
        return $this->belongsTo(StockPackage::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Method to dynamically get the reference model
    public function reference()
    {
        switch ($this->reference_type) {
            case self::REF_PURCHASE_RECEPTION:
                return $this->belongsTo(StockReceptionItem::class, 'reference_id');
            case self::REF_SALES_DELIVERY:
                return $this->belongsTo(StockDeliveryItem::class, 'reference_id');
            case self::REF_TRANSFER:
                return $this->belongsTo(StockTransferItem::class, 'reference_id');
            case self::REF_ADJUSTMENT:
                return $this->belongsTo(StockAdjustmentItem::class, 'reference_id');
            default:
                return null;
        }
    }

    // Static method to record a stock movement
    public static function recordMovement(
        $productId,
        $warehouseId,
        $referenceType,
        $referenceId,
        $movementType,
        $quantity,
        $unitCost,
        $stockPackageId = null,
        $notes = null
    ) {
        // Get the current stock
        $stock = ProductWarehouseStock::firstOrCreate(
            ['product_id' => $productId, 'warehouse_id' => $warehouseId],
            ['available_quantity' => 0, 'reserved_quantity' => 0, 'cmup' => 0]
        );

        $stockBefore = $stock->available_quantity;
        $cmupBefore = $stock->cmup;
        $totalCost = $quantity * $unitCost;

        // Update stock and CMUP based on movement type
        if ($movementType == self::MOVEMENT_IN) {
            // For incoming stock, update CMUP
            $stock->updateCMUP($quantity, $unitCost);
        } else {
            // For outgoing stock, just reduce the quantity
            $stock->reduceStock($quantity);
        }

        // Create the movement record
        $movement = self::create([
            'product_id' => $productId,
            'warehouse_id' => $warehouseId,
            'stock_package_id' => $stockPackageId,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'movement_type' => $movementType,
            'quantity' => $quantity,
            'unit_cost' => $unitCost,
            'total_cost' => $totalCost,
            'cmup_before' => $cmupBefore,
            'cmup_after' => $stock->cmup,
            'stock_before' => $stockBefore,
            'stock_after' => $stock->available_quantity,
            'created_by' => auth()->id(),
            'notes' => $notes,
        ]);

        return $movement;
    }
}