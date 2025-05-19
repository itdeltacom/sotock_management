<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockReceptionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_reception_id',
        'purchase_order_item_id',
        'product_id',
        'expected_quantity',
        'received_quantity',
        'unit_cost',
        'lot_number',
        'expiry_date',
        'notes',
    ];

    protected $casts = [
        'expected_quantity' => 'decimal:3',
        'received_quantity' => 'decimal:3',
        'unit_cost' => 'decimal:2',
        'expiry_date' => 'date',
    ];

    // Relationships
    public function stockReception()
    {
        return $this->belongsTo(StockReception::class);
    }

    public function purchaseOrderItem()
    {
        return $this->belongsTo(PurchaseOrderItem::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function stockPackage()
    {
        return $this->hasOne(StockPackage::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class, 'reference_id')
            ->where('reference_type', StockMovement::REF_PURCHASE_RECEPTION);
    }

    // Method to process the reception and update inventory
    public function processReception()
    {
        // Create or update the stock package
        $stockPackage = StockPackage::create([
            'product_id' => $this->product_id,
            'warehouse_id' => $this->stockReception->warehouse_id,
            'lot_number' => $this->lot_number,
            'expiry_date' => $this->expiry_date,
            'quantity' => $this->received_quantity,
            'cost' => $this->unit_cost,
            'available' => true,
            'notes' => $this->notes,
            'stock_reception_item_id' => $this->id,
        ]);

        // Record the stock movement and update CMUP
        StockMovement::recordMovement(
            $this->product_id,
            $this->stockReception->warehouse_id,
            StockMovement::REF_PURCHASE_RECEPTION,
            $this->id,
            StockMovement::MOVEMENT_IN,
            $this->received_quantity,
            $this->unit_cost,
            $stockPackage->id,
            'Stock reception from PO: ' . ($this->stockReception->purchase_order_id ?? 'Direct')
        );

        return $stockPackage;
    }

    // Method to recalculate expected quantity based on PO
    public function updateExpectedQuantity()
    {
        if ($this->purchase_order_item_id) {
            $poItem = $this->purchaseOrderItem;
            $alreadyReceived = $poItem->getTotalReceived() - $this->received_quantity;
            $this->expected_quantity = $poItem->quantity - $alreadyReceived;
            $this->save();
        }
        
        return $this->expected_quantity;
    }
}