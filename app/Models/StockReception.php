<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockReception extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_no',
        'purchase_order_id',
        'warehouse_id',
        'supplier_id',
        'received_by',
        'reception_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'reception_date' => 'date',
    ];

    // Define status constants
    const STATUS_PENDING = 'pending';
    const STATUS_COMPLETED = 'completed';
    const STATUS_PARTIAL = 'partial';

    // Relationships
    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function items()
    {
        return $this->hasMany(StockReceptionItem::class);
    }

    // Helper method to check if all expected items are received
    public function isComplete()
    {
        if (!$this->purchaseOrder) {
            return true; // Direct reception without PO is always complete
        }
        
        foreach ($this->items as $item) {
            if ($item->purchase_order_item_id) {
                $poItem = $item->purchaseOrderItem;
                
                if ($item->received_quantity < $poItem->quantity) {
                    return false;
                }
            }
        }
        
        return true;
    }

    // Method to update status based on received quantities
    public function updateStatus()
    {
        if ($this->isComplete()) {
            $this->status = self::STATUS_COMPLETED;
        } else {
            $this->status = self::STATUS_PARTIAL;
        }
        
        $this->save();
        
        // Update related purchase order status
        if ($this->purchase_order_id) {
            $this->purchaseOrder->updateReceiptStatus();
        }
        
        return $this->status;
    }

    // Static method to generate a new reference number
    public static function generateReferenceNumber()
    {
        $prefix = 'GR';
        $year = date('Y');
        $month = date('m');
        
        $lastReception = self::where('reference_no', 'like', "{$prefix}{$year}{$month}%")
            ->orderBy('reference_no', 'desc')
            ->first();
        
        if ($lastReception) {
            $lastNumber = (int) substr($lastReception->reference_no, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . $year . $month . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}