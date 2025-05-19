<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sales_order_id',
        'product_id',
        'quantity',
        'unit_price',
        'tax_rate',
        'tax_amount',
        'discount_rate',
        'discount_amount',
        'subtotal',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'unit_price' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_rate' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    // Relationships
    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function deliveryItems()
    {
        return $this->hasMany(StockDeliveryItem::class);
    }

    // Helper methods
    public function getTotalDelivered()
    {
        return $this->deliveryItems->sum('delivered_quantity');
    }

    public function getRemainingQuantity()
    {
        return $this->quantity - $this->getTotalDelivered();
    }

    public function isFullyDelivered()
    {
        return $this->getRemainingQuantity() <= 0;
    }

    // Method to calculate subtotal, tax, discount
    public function calculateAmounts()
    {
        $grossAmount = $this->quantity * $this->unit_price;
        
        // Calculate discount
        if ($this->discount_rate > 0) {
            $this->discount_amount = $grossAmount * ($this->discount_rate / 100);
        }
        
        $afterDiscount = $grossAmount - $this->discount_amount;
        
        // Calculate tax
        if ($this->tax_rate > 0) {
            $this->tax_amount = $afterDiscount * ($this->tax_rate / 100);
        }
        
        // Calculate subtotal (after tax and discount)
        $this->subtotal = $afterDiscount + $this->tax_amount;
        
        return $this->subtotal;
    }

    // Method to check stock availability
    public function checkStock()
    {
        $warehouseId = $this->salesOrder->warehouse_id;
        
        $stock = ProductWarehouseStock::where('product_id', $this->product_id)
            ->where('warehouse_id', $warehouseId)
            ->first();
        
        if (!$stock) {
            return 0;
        }
        
        return $stock->available_quantity;
    }
}