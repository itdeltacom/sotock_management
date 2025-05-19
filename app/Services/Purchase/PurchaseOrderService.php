<?php

namespace App\Services\Purchase;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Exception;

class PurchaseOrderService
{
    /**
     * Create a new purchase order
     *
     * @param array $data
     * @return PurchaseOrder
     */
    public function createOrder(array $data)
    {
        try {
            DB::beginTransaction();
            
            // Generate reference number if not provided
            if (!isset($data['reference_no'])) {
                $data['reference_no'] = PurchaseOrder::generateReferenceNumber();
            }
            
            // Set default status if not provided
            if (!isset($data['status'])) {
                $data['status'] = PurchaseOrder::STATUS_DRAFT;
            }
            
            // Set created_by if not provided
            if (!isset($data['created_by'])) {
                $data['created_by'] = auth()->id();
            }
            
            // Create the purchase order
            $purchaseOrder = PurchaseOrder::create([
                'reference_no' => $data['reference_no'],
                'supplier_id' => $data['supplier_id'],
                'warehouse_id' => $data['warehouse_id'],
                'created_by' => $data['created_by'],
                'order_date' => $data['order_date'],
                'expected_delivery_date' => $data['expected_delivery_date'] ?? null,
                'status' => $data['status'],
                'subtotal' => 0,
                'tax_amount' => 0,
                'discount_amount' => 0,
                'total_amount' => 0,
                'notes' => $data['notes'] ?? null,
            ]);
            
            // Add items to the purchase order
            $subtotal = 0;
            $taxAmount = 0;
            $discountAmount = 0;
            
            foreach ($data['items'] as $itemData) {
                $item = new PurchaseOrderItem([
                    'product_id' => $itemData['product_id'],
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'tax_rate' => $itemData['tax_rate'] ?? 0,
                    'tax_amount' => 0,
                    'discount_rate' => $itemData['discount_rate'] ?? 0,
                    'discount_amount' => 0,
                    'subtotal' => 0,
                    'notes' => $itemData['notes'] ?? null,
                ]);
                
                // Calculate amounts
                $item->calculateAmounts();
                
                // Add to purchase order
                $purchaseOrder->items()->save($item);
                
                // Update totals
                $subtotal += $item->subtotal;
                $taxAmount += $item->tax_amount;
                $discountAmount += $item->discount_amount;
            }
            
            // Update order totals
            $purchaseOrder->update([
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discountAmount,
                'total_amount' => $subtotal,
            ]);
            
            DB::commit();
            
            return $purchaseOrder->fresh(['items', 'supplier', 'warehouse']);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    /**
     * Update an existing purchase order
     *
     * @param PurchaseOrder $purchaseOrder
     * @param array $data
     * @return PurchaseOrder
     */
    public function updateOrder(PurchaseOrder $purchaseOrder, array $data)
    {
        try {
            DB::beginTransaction();
            
            // Check if order can be edited
            if (!$purchaseOrder->canBeEdited()) {
                throw new Exception('This purchase order cannot be edited.');
            }
            
            // Update basic info
            $purchaseOrder->update([
                'supplier_id' => $data['supplier_id'] ?? $purchaseOrder->supplier_id,
                'warehouse_id' => $data['warehouse_id'] ?? $purchaseOrder->warehouse_id,
                'order_date' => $data['order_date'] ?? $purchaseOrder->order_date,
                'expected_delivery_date' => $data['expected_delivery_date'] ?? $purchaseOrder->expected_delivery_date,
                'notes' => $data['notes'] ?? $purchaseOrder->notes,
            ]);
            
            // Update items if provided
            if (isset($data['items'])) {
                // Delete existing items
                $purchaseOrder->items()->delete();
                
                // Add new items
                $subtotal = 0;
                $taxAmount = 0;
                $discountAmount = 0;
                
                foreach ($data['items'] as $itemData) {
                    $item = new PurchaseOrderItem([
                        'product_id' => $itemData['product_id'],
                        'quantity' => $itemData['quantity'],
                        'unit_price' => $itemData['unit_price'],
                        'tax_rate' => $itemData['tax_rate'] ?? 0,
                        'tax_amount' => 0,
                        'discount_rate' => $itemData['discount_rate'] ?? 0,
                        'discount_amount' => 0,
                        'subtotal' => 0,
                        'notes' => $itemData['notes'] ?? null,
                    ]);
                    
                    // Calculate amounts
                    $item->calculateAmounts();
                    
                    // Add to purchase order
                    $purchaseOrder->items()->save($item);
                    
                    // Update totals
                    $subtotal += $item->subtotal;
                    $taxAmount += $item->tax_amount;
                    $discountAmount += $item->discount_amount;
                }
                
                // Update order totals
                $purchaseOrder->update([
                    'subtotal' => $subtotal,
                    'tax_amount' => $taxAmount,
                    'discount_amount' => $discountAmount,
                    'total_amount' => $subtotal,
                ]);
            }
            
            DB::commit();
            
            return $purchaseOrder->fresh(['items', 'supplier', 'warehouse']);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    /**
     * Confirm a purchase order
     *
     * @param PurchaseOrder $purchaseOrder
     * @return PurchaseOrder
     */
    public function confirmOrder(PurchaseOrder $purchaseOrder)
    {
        // Check if order can be confirmed
        if (!$purchaseOrder->canBeEdited()) {
            throw new Exception('This purchase order cannot be confirmed.');
        }
        
        // Update status
        $purchaseOrder->update([
            'status' => PurchaseOrder::STATUS_CONFIRMED
        ]);
        
        return $purchaseOrder->fresh();
    }
    
    /**
     * Cancel a purchase order
     *
     * @param PurchaseOrder $purchaseOrder
     * @return PurchaseOrder
     */
    public function cancelOrder(PurchaseOrder $purchaseOrder)
    {
        // Check if order can be cancelled
        if (!$purchaseOrder->canBeCancelled()) {
            throw new Exception('This purchase order cannot be cancelled.');
        }
        
        // Update status
        $purchaseOrder->update([
            'status' => PurchaseOrder::STATUS_CANCELLED
        ]);
        
        return $purchaseOrder->fresh();
    }
    
    /**
     * Get purchase orders with filtering
     *
     * @param array $filters
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getOrders(array $filters = [])
    {
        $query = PurchaseOrder::with(['supplier', 'warehouse', 'createdBy']);
        
        // Apply filters
        if (isset($filters['reference_no'])) {
            $query->where('reference_no', 'like', '%' . $filters['reference_no'] . '%');
        }
        
        if (isset($filters['supplier_id'])) {
            $query->where('supplier_id', $filters['supplier_id']);
        }
        
        if (isset($filters['warehouse_id'])) {
            $query->where('warehouse_id', $filters['warehouse_id']);
        }
        
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        if (isset($filters['date_from'])) {
            $query->where('order_date', '>=', $filters['date_from']);
        }
        
        if (isset($filters['date_to'])) {
            $query->where('order_date', '<=', $filters['date_to']);
        }
        
        // Order by
        $orderBy = $filters['order_by'] ?? 'created_at';
        $orderDir = $filters['order_dir'] ?? 'desc';
        $query->orderBy($orderBy, $orderDir);
        
        // Paginate
        $perPage = $filters['per_page'] ?? 15;
        
        return $query->paginate($perPage);
    }
}