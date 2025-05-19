<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockDelivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_no',
        'sales_order_id',
        'warehouse_id',
        'customer_id',
        'delivered_by',
        'delivery_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'delivery_date' => 'date',
    ];

    // Define status constants
    const STATUS_PENDING = 'pending';
    const STATUS_COMPLETED = 'completed';
    const STATUS_PARTIAL = 'partial';

    // Relationships
    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function deliveredBy()
    {
        return $this->belongsTo(User::class, 'delivered_by');
    }

    public function items()
    {
        return $this->hasMany(StockDeliveryItem::class);
    }

    // Helper method to check if all expected items are delivered
    public function isComplete()
    {
        if (!$this->sales_order_id) {
            return true; // Direct delivery without SO is always complete
        }
        
        foreach ($this->items as $item) {
            if ($item->sales_order_item_id) {
                $soItem = $item->salesOrderItem;
                
                if ($item->delivered_quantity < $soItem->quantity) {
                    return false;
                }
            }
        }
        
        return true;
    }

    // Method to update status based on delivered quantities
    public function updateStatus()
    {
        if ($this->isComplete()) {
            $this->status = self::STATUS_COMPLETED;
        } else {
            $this->status = self::STATUS_PARTIAL;
        }
        
        $this->save();
        
        // Update related sales order status
        if ($this->sales_order_id) {
            $this->salesOrder->updateDeliveryStatus();
        }
        
        return $this->status;
    }

    // Static method to generate a new reference number
    public static function generateReferenceNumber()
    {
        $prefix = 'DN';
        $year = date('Y');
        $month = date('m');
        
        $lastDelivery = self::where('reference_no', 'like', "{$prefix}{$year}{$month}%")
            ->orderBy('reference_no', 'desc')
            ->first();
        
        if ($lastDelivery) {
            $lastNumber = (int) substr($lastDelivery->reference_no, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . $year . $month . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}