<?php

namespace App\Services\Sales;

use App\Models\StockDelivery;
use App\Models\StockDeliveryItem;
use App\Models\SalesOrder;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\ProductWarehouseStock;
use Illuminate\Support\Facades\DB;
use Exception;

class StockDeliveryService
{
    /**
     * Create a new stock delivery from sales order
     *
     * @param SalesOrder $salesOrder
     * @param array $data
     * @return StockDelivery
     */
    public function createDeliveryFromSO(SalesOrder $salesOrder, array $data)
    {
        try {
            DB::beginTransaction();
            
            // Check if SO can be delivered
            if (!$salesOrder->canBeDelivered()) {
                throw new Exception('This sales order cannot be delivered.');
            }
            
            // Generate reference number if not provided
            if (!isset($data['reference_no'])) {
                $data['reference_no'] = StockDelivery::generateReferenceNumber();
            }
            
            // Set default status if not provided
            if (!isset($data['status'])) {
                $data['status'] = StockDelivery::STATUS_PENDING;
            }
            
            // Set delivered_by if not provided
            if (!isset($data['delivered_by'])) {
                $data['delivered_by'] = auth()->id();
            }
            
            // Create the stock delivery
            $delivery = StockDelivery::create([
                'reference_no' => $data['reference_no'],
                'sales_order_id' => $salesOrder->id,
                'warehouse_id' => $data['warehouse_id'] ?? $salesOrder->warehouse_id,
                'customer_id' => $salesOrder->customer_id,
                'delivered_by' => $data['delivered_by'],
                'delivery_date' => $data['delivery_date'] ?? now(),
                'status' => $data['status'],
                'notes' => $data['notes'] ?? null,
            ]);
            
            // Add items from the sales order
            foreach ($salesOrder->items as $soItem) {
                $remainingQty = $soItem->getRemainingQuantity();
                
                if ($remainingQty > 0) {
                    $deliveredQty = isset($data['items'][$soItem->id]) ? $data['items'][$soItem->id]['quantity'] : $remainingQty;
                    $unitPrice = $soItem->unit_price;
                    $lotNumber = isset($data['items'][$soItem->id]) ? ($data['items'][$soItem->id]['lot_number'] ?? null) : null;
                    $notes = isset($data['items'][$soItem->id]) ? ($data['items'][$soItem->id]['notes'] ?? null) : null;
                    
                    // Get CMUP cost
                    $stock = ProductWarehouseStock::where('product_id', $soItem->product_id)
                        ->where('warehouse_id', $delivery->warehouse_id)
                        ->first();
                    
                    // Create delivery item
                    $deliveryItem = StockDeliveryItem::create([
                        'stock_delivery_id' => $delivery->id,
                        'sales_order_item_id' => $soItem->id,
                        'product_id' => $soItem->product_id,
                        'lot_number' => $lotNumber,
                        'expected_quantity' => $remainingQty,
                        'delivered_quantity' => $deliveredQty,
                        'unit_cost' => $stock ? $stock->cmup : 0,
                        'unit_price' => $unitPrice,
                        'notes' => $notes,
                    ]);
                    
                    // Process the delivery
                    if ($delivery->status != StockDelivery::STATUS_PENDING) {
                        $deliveryItem->processDelivery();
                    }
                }
            }
            
            // Update status and related SO
            if ($delivery->status != StockDelivery::STATUS_PENDING) {
                $delivery->updateStatus();
            }
            
            DB::commit();
            
            return $delivery->fresh(['items', 'salesOrder', 'customer', 'warehouse']);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    /**
     * Create a direct stock delivery (without sales order)
     *
     * @param array $data
     * @return StockDelivery
     */
    public function createDirectDelivery(array $data)
    {
        try {
            DB::beginTransaction();
            
            // Generate reference number if not provided
            if (!isset($data['reference_no'])) {
                $data['reference_no'] = StockDelivery::generateReferenceNumber();
            }
            
            // Set default status if not provided
            if (!isset($data['status'])) {
                $data['status'] = StockDelivery::STATUS_PENDING;
            }
            
            // Set delivered_by if not provided
            if (!isset($data['delivered_by'])) {
                $data['delivered_by'] = auth()->id();
            }
            
            // Create the stock delivery
            $delivery = StockDelivery::create([
                'reference_no' => $data['reference_no'],
                'sales_order_id' => null,
                'warehouse_id' => $data['warehouse_id'],
                'customer_id' => $data['customer_id'],
                'delivered_by' => $data['delivered_by'],
                'delivery_date' => $data['delivery_date'] ?? now(),
                'status' => $data['status'],
                'notes' => $data['notes'] ?? null,
            ]);
            
            // Add items
            foreach ($data['items'] as $itemData) {
                // Get CMUP cost
                $stock = ProductWarehouseStock::where('product_id', $itemData['product_id'])
                    ->where('warehouse_id', $delivery->warehouse_id)
                    ->first();
                
                if (!$stock || $stock->available_quantity < $itemData['quantity']) {
                    throw new Exception('Insufficient stock for product ID: ' . $itemData['product_id']);
                }
                
                // Create delivery item
                $deliveryItem = StockDeliveryItem::create([
                    'stock_delivery_id' => $delivery->id,
                    'sales_order_item_id' => null,
                    'product_id' => $itemData['product_id'],
                    'lot_number' => $itemData['lot_number'] ?? null,
                    'expected_quantity' => $itemData['quantity'],
                    'delivered_quantity' => $itemData['quantity'],
                    'unit_cost' => $stock->cmup,
                    'unit_price' => $itemData['unit_price'],
                    'notes' => $itemData['notes'] ?? null,
                ]);
                
                // Process the delivery
                if ($delivery->status != StockDelivery::STATUS_PENDING) {
                    $deliveryItem->processDelivery();
                }
            }
            
            // Update status
            if ($delivery->status != StockDelivery::STATUS_PENDING) {
                $delivery->updateStatus();
            }
            
            DB::commit();
            
            return $delivery->fresh(['items', 'customer', 'warehouse']);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    /**
     * Process a pending delivery
     *
     * @param StockDelivery $delivery
     * @return StockDelivery
     */
    public function processDelivery(StockDelivery $delivery)
    {
        try {
            DB::beginTransaction();
            
            if ($delivery->status != StockDelivery::STATUS_PENDING) {
                throw new Exception('This delivery has already been processed.');
            }
            
            // Process each item
            foreach ($delivery->items as $item) {
                $item->processDelivery();
            }
            
            // Update status
            $delivery->status = StockDelivery::STATUS_COMPLETED;
            $delivery->save();
            
            // Update related sales order status if applicable
            if ($delivery->sales_order_id) {
                $delivery->salesOrder->updateDeliveryStatus();
            }
            
            DB::commit();
            
            return $delivery->fresh(['items', 'salesOrder', 'customer', 'warehouse']);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    /**
     * Get stock deliveries with filtering
     *
     * @param array $filters
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getDeliveries(array $filters = [])
    {
        $query = StockDelivery::with(['customer', 'warehouse', 'salesOrder', 'deliveredBy']);
        
        // Apply filters
        if (isset($filters['reference_no'])) {
            $query->where('reference_no', 'like', '%' . $filters['reference_no'] . '%');
        }
        
        if (isset($filters['sales_order_id'])) {
            $query->where('sales_order_id', $filters['sales_order_id']);
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
            $query->where('delivery_date', '>=', $filters['date_from']);
        }
        
        if (isset($filters['date_to'])) {
            $query->where('delivery_date', '<=', $filters['date_to']);
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