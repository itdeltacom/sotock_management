<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'reference_no',
        'supplier_id',
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
    const STATUS_PARTIALLY_RECEIVED = 'partially_received';
    const STATUS_RECEIVED = 'received';
    const STATUS_CANCELLED = 'cancelled';

    // Relationships
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
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
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function receptions()
    {
        return $this->hasMany(StockReception::class);
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

    public function canBeReceived()
    {
        return in_array($this->status, [self::STATUS_CONFIRMED, self::STATUS_PARTIALLY_RECEIVED]);
    }

    public function getTotalReceivedItems()
    {
        $totalReceived = [];
        
        foreach ($this->items as $item) {
            $productId = $item->product_id;
            $totalReceived[$productId] = 0;
            
            foreach ($this->receptions as $reception) {
                foreach ($reception->items as $receptionItem) {
                    if ($receptionItem->product_id == $productId) {
                        $totalReceived[$productId] += $receptionItem->received_quantity;
                    }
                }
            }
        }
        
        return $totalReceived;
    }

    // Method to update order status based on received items
    public function updateReceiptStatus()
    {
        $allItemsReceived = true;
        $someItemsReceived = false;
        $totalReceived = $this->getTotalReceivedItems();
        
        foreach ($this->items as $item) {
            $received = $totalReceived[$item->product_id] ?? 0;
            
            if ($received <= 0) {
                $allItemsReceived = false;
            } elseif ($received > 0) {
                $someItemsReceived = true;
                
                if ($received < $item->quantity) {
                    $allItemsReceived = false;
                }
            }
        }
        
        if ($allItemsReceived) {
            $this->status = self::STATUS_RECEIVED;
        } elseif ($someItemsReceived) {
            $this->status = self::STATUS_PARTIALLY_RECEIVED;
        }
        
        $this->save();
        
        return $this->status;
    }

    // Static method to generate a new reference number
    public static function generateReferenceNumber()
    {
        $prefix = 'PO';
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