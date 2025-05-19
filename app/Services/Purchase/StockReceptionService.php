<?php

namespace App\Services\Purchase;

use App\Models\StockReception;
use App\Models\StockReceptionItem;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use Exception;

class StockReceptionService
{
    /**
     * Create a new stock reception from purchase order
     *
     * @param PurchaseOrder $purchaseOrder
     * @param array $data
     * @return StockReception
     */
    public function createReceptionFromPO(PurchaseOrder $purchaseOrder, array $data)
    {
        try {
            DB::beginTransaction();
            
            // Check if PO can be received
            if (!$purchaseOrder->canBeReceived()) {
                throw new Exception('This purchase order cannot be received.');
            }
            
            // Generate reference number if not provided
            if (!isset($data['reference_no'])) {
                $data['reference_no'] = StockReception::generateReferenceNumber();
            }
            
            // Set default status if not provided
            if (!isset($data['status'])) {
                $data['status'] = StockReception::STATUS_PENDING;
            }
            
            // Set received_by if not provided
            if (!isset($data['received_by'])) {
                $data['received_by'] = auth()->id();
            }
            
            // Create the stock reception
            $reception = StockReception::create([
                'reference_no' => $data['reference_no'],
                'purchase_order_id' => $purchaseOrder->id,
                'warehouse_id' => $data['warehouse_id'] ?? $purchaseOrder->warehouse_id,
                'supplier_id' => $purchaseOrder->supplier_id,
                'received_by' => $data['received_by'],
                'reception_date' => $data['reception_date'] ?? now(),
                'status' => $data['status'],
                'notes' => $data['notes'] ?? null,
            ]);
            
            // Add items from the purchase order
            foreach ($purchaseOrder->items as $poItem) {
                $remainingQty = $poItem->getRemainingQuantity();
                
                if ($remainingQty > 0) {
                    $receivedQty = isset($data['items'][$poItem->id]) ? $data['items'][$poItem->id]['quantity'] : $remainingQty;
                    $unitCost = isset($data['items'][$poItem->id]) ? $data['items'][$poItem->id]['unit_cost'] : $poItem->unit_price;
                    $lotNumber = isset($data['items'][$poItem->id]) ? ($data['items'][$poItem->id]['lot_number'] ?? null) : null;
                    $expiryDate = isset($data['items'][$poItem->id]) ? ($data['items'][$poItem->id]['expiry_date'] ?? null) : null;
                    $notes = isset($data['items'][$poItem->id]) ? ($data['items'][$poItem->id]['notes'] ?? null) : null;
                    
                    // Create reception item
                    $receptionItem = StockReceptionItem::create([
                        'stock_reception_id' => $reception->id,
                        'purchase_order_item_id' => $poItem->id,
                        'product_id' => $poItem->product_id,
                        'expected_quantity' => $remainingQty,
                        'received_quantity' => $receivedQty,
                        'unit_cost' => $unitCost,
                        'lot_number' => $lotNumber,
                        'expiry_date' => $expiryDate,
                        'notes' => $notes,
                    ]);
                    
                    // Process the reception
                    if ($reception->status != StockReception::STATUS_PENDING) {
                        $receptionItem->processReception();
                    }
                }
            }
            
            // Update status and related PO
            if ($reception->status != StockReception::STATUS_PENDING) {
                $reception->updateStatus();
            }
            
            DB::commit();
            
            return $reception->fresh(['items', 'purchaseOrder', 'supplier', 'warehouse']);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    /**
     * Create a direct stock reception (without purchase order)
     *
     * @param array $data
     * @return StockReception
     */
    public function createDirectReception(array $data)
    {
        try {
            DB::beginTransaction();
            
            // Generate reference number if not provided
            if (!isset($data['reference_no'])) {
                $data['reference_no'] = StockReception::generateReferenceNumber();
            }
            
            // Set default status if not provided
            if (!isset($data['status'])) {
                $data['status'] = StockReception::STATUS_PENDING;
            }
            
            // Set received_by if not provided
            if (!isset($data['received_by'])) {
                $data['received_by'] = auth()->id();
            }
            
            // Create the stock reception
            $reception = StockReception::create([
                'reference_no' => $data['reference_no'],
                'purchase_order_id' => null,
                'warehouse_id' => $data['warehouse_id'],
                'supplier_id' => $data['supplier_id'],
                'received_by' => $data['received_by'],
                'reception_date' => $data['reception_date'] ?? now(),
                'status' => $data['status'],
                'notes' => $data['notes'] ?? null,
            ]);
            
            // Add items
            foreach ($data['items'] as $itemData) {
                // Create reception item
                $receptionItem = StockReceptionItem::create([
                    'stock_reception_id' => $reception->id,
                    'purchase_order_item_id' => null,
                    'product_id' => $itemData['product_id'],
                    'expected_quantity' => $itemData['quantity'],
                    'received_quantity' => $itemData['quantity'],
                    'unit_cost' => $itemData['unit_cost'],
                    'lot_number' => $itemData['lot_number'] ?? null,
                    'expiry_date' => $itemData['expiry_date'] ?? null,
                    'notes' => $itemData['notes'] ?? null,
                ]);
                
                // Process the reception
                if ($reception->status != StockReception::STATUS_PENDING) {
                    $receptionItem->processReception();
                }
            }
            
            // Update status
            if ($reception->status != StockReception::STATUS_PENDING) {
                $reception->updateStatus();
            }
            
            DB::commit();
            
            return $reception->fresh(['items', 'supplier', 'warehouse']);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    /**
     * Process a pending reception
     *
     * @param StockReception $reception
     * @return StockReception
     */
    public function processReception(StockReception $reception)
    {
        try {
            DB::beginTransaction();
            
            if ($reception->status != StockReception::STATUS_PENDING) {
                throw new Exception('This reception has already been processed.');
            }
            
            // Process each item
            foreach ($reception->items as $item) {
                $item->processReception();
            }
            
            // Update status
            $reception->status = StockReception::STATUS_COMPLETED;
            $reception->save();
            
            // Update related purchase order status if applicable
            if ($reception->purchase_order_id) {
                $reception->purchaseOrder->updateReceiptStatus();
            }
            
            DB::commit();
            
            return $reception->fresh(['items', 'purchaseOrder', 'supplier', 'warehouse']);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    /**
     * Get stock receptions with filtering
     *
     * @param array $filters
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getReceptions(array $filters = [])
    {
        $query = StockReception::with(['supplier', 'warehouse', 'purchaseOrder', 'receivedBy']);
        
        // Apply filters
        if (isset($filters['reference_no'])) {
            $query->where('reference_no', 'like', '%' . $filters['reference_no'] . '%');
        }
        
        if (isset($filters['purchase_order_id'])) {
            $query->where('purchase_order_id', $filters['purchase_order_id']);
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
            $query->where('reception_date', '>=', $filters['date_from']);
        }
        
        if (isset($filters['date_to'])) {
            $query->where('reception_date', '<=', $filters['date_to']);
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