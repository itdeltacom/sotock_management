<?php

namespace App\Services\Sales;

use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\ProductWarehouseStock;
use Illuminate\Support\Facades\DB;
use Exception;

class SalesOrderService
{
    /**
     * Create a new sales order
     *
     * @param array $data
     * @return SalesOrder
     */
    public function createOrder(array $data)
    {
        try {
            DB::beginTransaction();
            
            // Generate reference number if not provided
            if (!isset($data['reference_no'])) {
                $data['reference_no'] = SalesOrder::generateReferenceNumber();
            }
            
            // Set default status if not provided
            if (!isset($data['status'])) {
                $data['status'] = SalesOrder::STATUS_DRAFT;
            }
            
            // Set created_by if not provided
            if (!isset($data['created_by'])) {
                $data['created_by'] = auth()->id();
            }
            
            // Create the sales order
            $salesOrder = SalesOrder::create([
                'reference_no' => $data['reference_no'],
                'customer_id' => $data['customer_id'],
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
            
            // Add items to the sales order
            $subtotal = 0;
            $taxAmount = 0;
            $discountAmount = 0;
            
            foreach ($data['items'] as $itemData) {
                $item = new SalesOrderItem([
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
                
                // Add to sales order
                $salesOrder->items()->save($item);
                
                // Update totals
                $subtotal += $item->subtotal;
                $taxAmount += $item->tax_amount;
                $discountAmount += $item->discount_amount;
            }
            
            // Update order totals
            $salesOrder->update([
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discountAmount,
                'total_amount' => $subtotal,
            ]);
            
            DB::commit();
            
            return $salesOrder->fresh(['items', 'customer', 'warehouse']);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    /**
     * Update an existing sales order
     *
     * @param SalesOrder $salesOrder
     * @param array $data
     * @return SalesOrder
     */
    public function updateOrder(SalesOrder $salesOrder, array $data)
    {
        try {
            DB::beginTransaction();
            
            // Check if order can be edited
            if (!$salesOrder->canBeEdited()) {
                throw new Exception('This sales order cannot be edited.');
            }
            
            // Update basic info
            $salesOrder->update([
                'customer_id' => $data['customer_id'] ?? $salesOrder->customer_id,
                'warehouse_id' => $data['warehouse_id'] ?? $salesOrder->warehouse_id,
                'order_date' => $data['order_date'] ?? $salesOrder->order_date,
                'expected_delivery_date' => $data['expected_delivery_date'] ?? $salesOrder->expected_delivery_date,
                'notes' => $data['notes'] ?? $salesOrder->notes,
            ]);
            
            // Update items if provided
            if (isset($data['items'])) {
                // Delete existing items
                $salesOrder->items()->delete();
                
                // Add new items
                $subtotal = 0;
                $taxAmount = 0;
                $discountAmount = 0;
                
                foreach ($data['items'] as $itemData) {
                    $item = new SalesOrderItem([
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
                    
                    // Add to sales order
                    $salesOrder->items()->save($item);
                    
                    // Update totals
                    $subtotal += $item->subtotal;
                    $taxAmount += $item->tax_amount;
                    $discountAmount += $item->discount_amount;
                }
                
                // Update order totals
                $salesOrder->update([
                    'subtotal' => $subtotal,
                    'tax_amount' => $taxAmount,
                    'discount_amount' => $discountAmount,
                    'total_amount' => $subtotal,
                ]);
            }
            
            DB::commit();
            
            return $salesOrder->fresh(['items', 'customer', 'warehouse']);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    /**
     * Confirm a sales order
     *
     * @param SalesOrder $salesOrder
     * @return SalesOrder
     */
    public function confirmOrder(SalesOrder $salesOrder)
    {
        // Check if order can be confirmed
        if (!$salesOrder->canBeEdited()) {
            throw new Exception('This sales order cannot be confirmed.');
        }
        
        // Check stock availability
        $insufficientStock = $salesOrder->checkStock();
        if (count($insufficientStock) > 0) {
            $errorMessage = 'Insufficient stock for the following products: ';
            foreach ($insufficientStock as $item) {
                $errorMessage .= $item['product'] . ' (Required: ' . $item['required'] . ', Available: ' . $item['available'] . '), ';
            }
            throw new Exception(rtrim($errorMessage, ', '));
        }
        
        // Update status
        $salesOrder->update([
            'status' => SalesOrder::STATUS_CONFIRMED
        ]);
        
        // Reserve stock
        $this->reserveStockForOrder($salesOrder);
        
        return $salesOrder->fresh();
    }
    
    /**
     * Cancel a sales order
     *
     * @param SalesOrder $salesOrder
     * @return SalesOrder
     */
    public function cancelOrder(SalesOrder $salesOrder)
    {
        // Check if order can be cancelled
        if (!$salesOrder->canBeCancelled()) {
            throw new Exception('This sales order cannot be cancelled.');
        }
        
        // Update status
        $salesOrder->update([
            'status' => SalesOrder::STATUS_CANCELLED
        ]);
        
        // Release reserved stock
        if ($salesOrder->status == SalesOrder::STATUS_CONFIRMED) {
            $this->releaseReservedStockForOrder($salesOrder);
        }
        
        return $salesOrder->fresh();
    }
    
    /**
     * Reserve stock for a confirmed order
     *
     * @param SalesOrder $salesOrder
     * @return bool
     */
    private function reserveStockForOrder(SalesOrder $salesOrder)
    {
        $warehouseId = $salesOrder->warehouse_id;
        
        foreach ($salesOrder->items as $item) {
            $stock = ProductWarehouseStock::where('product_id', $item->product_id)
                ->where('warehouse_id', $warehouseId)
                ->firstOrFail();
            
            $stock->reserveStock($item->quantity);
        }
        
        return true;
    }
    
    /**
     * Release reserved stock for a cancelled order
     *
     * @param SalesOrder $salesOrder
     * @return bool
     */
    private function releaseReservedStockForOrder(SalesOrder $salesOrder)
    {
        $warehouseId = $salesOrder->warehouse_id;
        
        foreach ($salesOrder->items as $item) {
            $stock = ProductWarehouseStock::where('product_id', $item->product_id)
                ->where('warehouse_id', $warehouseId)
                ->firstOrFail();
            
            $stock->releaseReservedStock($item->quantity);
        }
        
        return true;
    }
    
    /**
     * Get sales orders with filtering
     *
     * @param array $filters
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getOrders(array $filters = [])
    {
        $query = SalesOrder::with(['customer', 'warehouse', 'createdBy']);
        
        // Apply filters
        if (isset($filters['reference_no'])) {
            $query->where('reference_no', 'like', '%' . $filters['reference_no'] . '%');
        }
        
        if (isset($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
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