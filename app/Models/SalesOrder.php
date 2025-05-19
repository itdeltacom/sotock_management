<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'reference_no',
        'customer_id',
        'warehouse_id',
        'created_by',
        'order_date',
        'expected_delivery_date',
        'status',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'notes',
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_delivery_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    // Define status constants
    const STATUS_DRAFT = 'draft';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_PARTIALLY_DELIVERED = 'partially_delivered';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_CANCELLED = 'cancelled';

    // Relationships
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(SalesOrderItem::class);
    }

    public function deliveries()
    {
        return $this->hasMany(StockDelivery::class);
    }

    // Helper methods
    public function canBeEdited()
    {
        return in_array($this->status, [self::STATUS_DRAFT]);
    }

    public function canBeCancelled()
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_CONFIRMED]);
    }

    public function canBeDelivered()
    {
        return in_array($this->status, [self::STATUS_CONFIRMED, self::STATUS_PARTIALLY_DELIVERED]);
    }

    public function getTotalDeliveredItems()
    {
        $totalDelivered = [];
        
        foreach ($this->items as $item) {
            $productId = $item->product_id;
            $totalDelivered[$productId] = 0;
            
            foreach ($this->deliveries as $delivery) {
                foreach ($delivery->items as $deliveryItem) {
                    if ($deliveryItem->product_id == $productId) {
                        $totalDelivered[$productId] += $deliveryItem->delivered_quantity;
                    }
                }
            }
        }
        
        return $totalDelivered;
    }

    // Method to update order status based on delivered items
    public function updateDeliveryStatus()
    {
        $allItemsDelivered = true;
        $someItemsDelivered = false;
        $totalDelivered = $this->getTotalDeliveredItems();
        
        foreach ($this->items as $item) {
            $delivered = $totalDelivered[$item->product_id] ?? 0;
            
            if ($delivered <= 0) {
                $allItemsDelivered = false;
            } elseif ($delivered > 0) {
                $someItemsDelivered = true;
                
                if ($delivered < $item->quantity) {
                    $allItemsDelivered = false;
                }
            }
        }
        
        if ($allItemsDelivered) {
            $this->status = self::STATUS_DELIVERED;
        } elseif ($someItemsDelivered) {
            $this->status = self::STATUS_PARTIALLY_DELIVERED;
        }
        
        $this->save();
        
        return $this->status;
    }

    // Check if all products are in stock
    public function checkStock()
    {
        $insufficientStock = [];
        
        foreach ($this->items as $item) {
            $stock = ProductWarehouseStock::where('product_id', $item->product_id)
                ->where('warehouse_id', $this->warehouse_id)
                ->first();
            
            if (!$stock || $stock->available_quantity < $item->quantity) {
                $insufficientStock[] = [
                    'product' => $item->product->name,
                    'required' => $item->quantity,
                    'available' => $stock ? $stock->available_quantity : 0
                ];
            }
        }
        
        return $insufficientStock;
    }

    // Static method to generate a new reference number
    public static function generateReferenceNumber()
    {
        $prefix = 'SO';
        $year = date('Y');
        $month = date('m');
        
        $lastOrder = self::where('reference_no', 'like', "{$prefix}{$year}{$month}%")
            ->orderBy('reference_no', 'desc')
            ->first();
        
        if ($lastOrder) {
            $lastNumber = (int) substr($lastOrder->reference_no, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . $year . $month . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}