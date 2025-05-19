<?php

namespace App\Services\Warehouse;

use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Models\StockAdjustment;
use App\Models\StockAdjustmentItem;
use App\Models\StockPackage;
use App\Models\ProductWarehouseStock;
use App\Models\StockMovement;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use Exception;

class WarehouseService
{
    /**
     * Create a new stock transfer between warehouses
     *
     * @param array $data
     * @return StockTransfer
     */
    public function createStockTransfer(array $data)
    {
        try {
            DB::beginTransaction();
            
            // Check if source and destination warehouses are different
            if ($data['source_warehouse_id'] == $data['destination_warehouse_id']) {
                throw new Exception('Source and destination warehouses must be different.');
            }
            
            // Generate reference number if not provided
            if (!isset($data['reference_no'])) {
                $data['reference_no'] = $this->generateTransferReferenceNumber();
            }
            
            // Set default status if not provided
            if (!isset($data['status'])) {
                $data['status'] = 'draft';
            }
            
            // Set created_by if not provided
            if (!isset($data['created_by'])) {
                $data['created_by'] = auth()->id();
            }
            
            // Create the stock transfer
            $transfer = StockTransfer::create([
                'reference_no' => $data['reference_no'],
                'source_warehouse_id' => $data['source_warehouse_id'],
                'destination_warehouse_id' => $data['destination_warehouse_id'],
                'created_by' => $data['created_by'],
                'transfer_date' => $data['transfer_date'] ?? now(),
                'status' => $data['status'],
                'notes' => $data['notes'] ?? null,
            ]);
            
            // Add items to the transfer
            foreach ($data['items'] as $itemData) {
                $productId = $itemData['product_id'];
                $quantity = $itemData['quantity'];
                $sourcePackageId = $itemData['source_package_id'] ?? null;
                
                // Check stock availability
                $sourceStock = ProductWarehouseStock::where('product_id', $productId)
                    ->where('warehouse_id', $data['source_warehouse_id'])
                    ->first();
                
                if (!$sourceStock || $sourceStock->available_quantity < $quantity) {
                    throw new Exception('Insufficient stock for product ID: ' . $productId);
                }
                
                // Get unit cost (CMUP)
                $unitCost = $sourceStock->cmup;
                
                // If a specific package is specified, check its availability
                if ($sourcePackageId) {
                    $package = StockPackage::where('id', $sourcePackageId)
                        ->where('product_id', $productId)
                        ->where('warehouse_id', $data['source_warehouse_id'])
                        ->where('available', true)
                        ->first();
                    
                    if (!$package || $package->quantity < $quantity) {
                        throw new Exception('Insufficient quantity in specified package.');
                    }
                    
                    $lotNumber = $package->lot_number;
                    $expiryDate = $package->expiry_date;
                } else {
                    $lotNumber = $itemData['lot_number'] ?? null;
                    $expiryDate = $itemData['expiry_date'] ?? null;
                }
                
                // Create transfer item
                $transferItem = StockTransferItem::create([
                    'stock_transfer_id' => $transfer->id,
                    'product_id' => $productId,
                    'source_package_id' => $sourcePackageId,
                    'quantity' => $quantity,
                    'unit_cost' => $unitCost,
                    'lot_number' => $lotNumber,
                    'expiry_date' => $expiryDate,
                    'notes' => $itemData['notes'] ?? null,
                ]);
                
                // Process the transfer if status is not draft
                if ($transfer->status != 'draft') {
                    $this->processTransferItem($transferItem);
                }
            }
            
            DB::commit();
            
            return $transfer->fresh(['items']);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    /**
     * Process a transfer item (update stock)
     *
     * @param StockTransferItem $item
     * @return bool
     */
    public function processTransferItem(StockTransferItem $item)
    {
        try {
            DB::beginTransaction();
            
            $transfer = $item->stockTransfer;
            $sourceWarehouseId = $transfer->source_warehouse_id;
            $destinationWarehouseId = $transfer->destination_warehouse_id;
            $productId = $item->product_id;
            $quantity = $item->quantity;
            $unitCost = $item->unit_cost;
            
            // Reduce stock from source warehouse
            $sourceStock = ProductWarehouseStock::where('product_id', $productId)
                ->where('warehouse_id', $sourceWarehouseId)
                ->firstOrFail();
            
            $sourceStock->reduceStock($quantity);
            
            // If a specific package is specified, reduce from that package
            if ($item->source_package_id) {
                $sourcePackage = StockPackage::findOrFail($item->source_package_id);
                $sourcePackage->reduceQuantity($quantity);
            } else {
                // Otherwise, reduce from packages using FIFO
                $this->reduceStockFromPackages($productId, $sourceWarehouseId, $quantity);
            }
            
            // Record the stock movement (outgoing from source)
            StockMovement::recordMovement(
                $productId,
                $sourceWarehouseId,
                StockMovement::REF_TRANSFER,
                $item->id,
                StockMovement::MOVEMENT_OUT,
                $quantity,
                $unitCost,
                $item->source_package_id,
                'Transfer to warehouse ID: ' . $destinationWarehouseId
            );
            
            // Add stock to destination warehouse
            $destinationStock = ProductWarehouseStock::firstOrCreate(
                [
                    'product_id' => $productId,
                    'warehouse_id' => $destinationWarehouseId
                ],
                [
                    'available_quantity' => 0,
                    'reserved_quantity' => 0,
                    'cmup' => 0
                ]
            );
            
            // Create a new package in the destination warehouse
            $destinationPackage = StockPackage::create([
                'product_id' => $productId,
                'warehouse_id' => $destinationWarehouseId,
                'lot_number' => $item->lot_number,
                'expiry_date' => $item->expiry_date,
                'quantity' => $quantity,
                'cost' => $unitCost,
                'available' => true,
                'notes' => 'Transferred from warehouse ID: ' . $sourceWarehouseId,
            ]);
            
            // Update CMUP and quantity in destination warehouse
            $destinationStock->updateCMUP($quantity, $unitCost);
            
            // Record the stock movement (incoming to destination)
            StockMovement::recordMovement(
                $productId,
                $destinationWarehouseId,
                StockMovement::REF_TRANSFER,
                $item->id,
                StockMovement::MOVEMENT_IN,
                $quantity,
                $unitCost,
                $destinationPackage->id,
                'Transfer from warehouse ID: ' . $sourceWarehouseId
            );
            
            DB::commit();
            
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    /**
     * Reduce stock from packages using FIFO (First-In-First-Out)
     *
     * @param int $productId
     * @param int $warehouseId
     * @param float $quantity
     * @return bool
     */
    private function reduceStockFromPackages($productId, $warehouseId, $quantity)
    {
        $remainingQuantity = $quantity;
        
        // Get available packages ordered by creation date (FIFO)
        $packages = StockPackage::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->where('available', true)
            ->where('quantity', '>', 0)
            ->orderBy('created_at', 'asc')
            ->get();
        
        foreach ($packages as $package) {
            if ($remainingQuantity <= 0) {
                break;
            }
            
            $quantityToTake = min($package->quantity, $remainingQuantity);
            $package->reduceQuantity($quantityToTake);
            $remainingQuantity -= $quantityToTake;
        }
        
        if ($remainingQuantity > 0) {
            throw new Exception('Insufficient stock in packages for product ID: ' . $productId);
        }
        
        return true;
    }
    
    /**
     * Confirm and process a stock transfer
     *
     * @param StockTransfer $transfer
     * @return StockTransfer
     */
    public function confirmTransfer(StockTransfer $transfer)
    {
        try {
            DB::beginTransaction();
            
            if ($transfer->status != 'draft') {
                throw new Exception('This transfer has already been processed.');
            }
            
            // Process each item
            foreach ($transfer->items as $item) {
                $this->processTransferItem($item);
            }
            
            // Update status
            $transfer->status = 'completed';
            $transfer->save();
            
            DB::commit();
            
            return $transfer->fresh();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    /**
     * Create a stock adjustment
     *
     * @param array $data
     * @return StockAdjustment
     */
    public function createStockAdjustment(array $data)
    {
        try {
            DB::beginTransaction();
            
            // Generate reference number if not provided
            if (!isset($data['reference_no'])) {
                $data['reference_no'] = $this->generateAdjustmentReferenceNumber();
            }
            
            // Set default status if not provided
            if (!isset($data['status'])) {
                $data['status'] = 'draft';
            }
            
            // Set created_by if not provided
            if (!isset($data['created_by'])) {
                $data['created_by'] = auth()->id();
            }
            
            // Create the stock adjustment
            $adjustment = StockAdjustment::create([
                'reference_no' => $data['reference_no'],
                'warehouse_id' => $data['warehouse_id'],
                'created_by' => $data['created_by'],
                'adjustment_date' => $data['adjustment_date'] ?? now(),
                'type' => $data['type'], // addition or subtraction
                'reason' => $data['reason'],
                'status' => $data['status'],
                'notes' => $data['notes'] ?? null,
            ]);
            
            // Add items to the adjustment
            foreach ($data['items'] as $itemData) {
                $productId = $itemData['product_id'];
                $quantity = $itemData['quantity'];
                $stockPackageId = $itemData['stock_package_id'] ?? null;
                
                // Get the stock
                $stock = ProductWarehouseStock::where('product_id', $productId)
                    ->where('warehouse_id', $data['warehouse_id'])
                    ->first();
                
                // For subtraction, check stock availability
                if ($data['type'] == 'subtraction') {
                    if (!$stock || $stock->available_quantity < $quantity) {
                        throw new Exception('Insufficient stock for product ID: ' . $productId);
                    }
                    
                    // If a specific package is specified, check its availability
                    if ($stockPackageId) {
                        $package = StockPackage::where('id', $stockPackageId)
                            ->where('product_id', $productId)
                            ->where('warehouse_id', $data['warehouse_id'])
                            ->where('available', true)
                            ->first();
                        
                        if (!$package || $package->quantity < $quantity) {
                            throw new Exception('Insufficient quantity in specified package.');
                        }
                        
                        $lotNumber = $package->lot_number;
                    } else {
                        $lotNumber = $itemData['lot_number'] ?? null;
                    }
                } else {
                    $lotNumber = $itemData['lot_number'] ?? null;
                }
                
                // Get unit cost
                $unitCost = $stock ? $stock->cmup : ($itemData['unit_cost'] ?? 0);
                
                // Create adjustment item
                $adjustmentItem = StockAdjustmentItem::create([
                    'stock_adjustment_id' => $adjustment->id,
                    'product_id' => $productId,
                    'stock_package_id' => $stockPackageId,
                    'quantity' => $quantity,
                    'unit_cost' => $unitCost,
                    'lot_number' => $lotNumber,
                    'notes' => $itemData['notes'] ?? null,
                ]);
                
                // Process the adjustment if status is confirmed
                if ($adjustment->status == 'confirmed') {
                    $this->processAdjustmentItem($adjustmentItem);
                }
            }
            
            DB::commit();
            
            return $adjustment->fresh(['items']);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    /**
     * Process an adjustment item (update stock)
     *
     * @param StockAdjustmentItem $item
     * @return bool
     */
    public function processAdjustmentItem(StockAdjustmentItem $item)
    {
        try {
            DB::beginTransaction();
            
            $adjustment = $item->stockAdjustment;
            $warehouseId = $adjustment->warehouse_id;
            $productId = $item->product_id;
            $quantity = $item->quantity;
            $unitCost = $item->unit_cost;
            
            // Get or create stock record
            $stock = ProductWarehouseStock::firstOrCreate(
                [
                    'product_id' => $productId,
                    'warehouse_id' => $warehouseId
                ],
                [
                    'available_quantity' => 0,
                    'reserved_quantity' => 0,
                    'cmup' => 0
                ]
            );
            
            if ($adjustment->type == 'addition') {
                // Add stock
                // Create a new package
                $package = StockPackage::create([
                    'product_id' => $productId,
                    'warehouse_id' => $warehouseId,
                    'lot_number' => $item->lot_number,
                    'quantity' => $quantity,
                    'cost' => $unitCost,
                    'available' => true,
                    'notes' => 'Stock adjustment: ' . $adjustment->reason,
                ]);
                
                // Update CMUP and quantity
                $stock->updateCMUP($quantity, $unitCost);
                
                // Record the stock movement
                StockMovement::recordMovement(
                    $productId,
                    $warehouseId,
                    StockMovement::REF_ADJUSTMENT,
                    $item->id,
                    StockMovement::MOVEMENT_IN,
                    $quantity,
                    $unitCost,
                    $package->id,
                    'Stock adjustment (addition): ' . $adjustment->reason
                );
            } else {
                // Reduce stock
                $stock->reduceStock($quantity);
                
                // If a specific package is specified, reduce from that package
                if ($item->stock_package_id) {
                    $package = StockPackage::findOrFail($item->stock_package_id);
                    $package->reduceQuantity($quantity);
                    $packageId = $package->id;
                } else {
                    // Otherwise, reduce from packages using FIFO
                    $this->reduceStockFromPackages($productId, $warehouseId, $quantity);
                    $packageId = null;
                }
                
                // Record the stock movement
                StockMovement::recordMovement(
                    $productId,
                    $warehouseId,
                    StockMovement::REF_ADJUSTMENT,
                    $item->id,
                    StockMovement::MOVEMENT_OUT,
                    $quantity,
                    $unitCost,
                    $packageId,
                    'Stock adjustment (subtraction): ' . $adjustment->reason
                );
            }
            
            DB::commit();
            
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    /**
     * Confirm and process a stock adjustment
     *
     * @param StockAdjustment $adjustment
     * @return StockAdjustment
     */
    public function confirmAdjustment(StockAdjustment $adjustment)
    {
        try {
            DB::beginTransaction();
            
            if ($adjustment->status != 'draft') {
                throw new Exception('This adjustment has already been processed.');
            }
            
            // Process each item
            foreach ($adjustment->items as $item) {
                $this->processAdjustmentItem($item);
            }
            
            // Update status
            $adjustment->status = 'confirmed';
            $adjustment->save();
            
            DB::commit();
            
            return $adjustment->fresh();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    /**
     * Get product stock information across all warehouses
     *
     * @param Product $product
     * @return array
     */
    public function getProductStock(Product $product)
    {
        $stockInfo = [
            'product' => $product,
            'total_quantity' => 0,
            'total_value' => 0,
            'global_cmup' => 0,
            'warehouses' => []
        ];
        
        // Get stock in all warehouses
        $warehouseStock = ProductWarehouseStock::where('product_id', $product->id)
            ->with('warehouse')
            ->get();
        
        foreach ($warehouseStock as $stock) {
            $stockInfo['warehouses'][] = [
                'warehouse' => $stock->warehouse,
                'available_quantity' => $stock->available_quantity,
                'reserved_quantity' => $stock->reserved_quantity,
                'total_quantity' => $stock->getTotalQuantity(),
                'cmup' => $stock->cmup,
                'value' => $stock->available_quantity * $stock->cmup,
                'min_stock' => $stock->min_stock,
                'max_stock' => $stock->max_stock,
                'is_low_stock' => $stock->isLowStock(),
                'is_over_stock' => $stock->isOverStock()
            ];
            
            $stockInfo['total_quantity'] += $stock->available_quantity;
            $stockInfo['total_value'] += $stock->available_quantity * $stock->cmup;
        }
        
        // Calculate global CMUP
        if ($stockInfo['total_quantity'] > 0) {
            $stockInfo['global_cmup'] = $stockInfo['total_value'] / $stockInfo['total_quantity'];
        }
        
        return $stockInfo;
    }
    
    /**
     * Get warehouse stock information with optional filtering
     *
     * @param Warehouse $warehouse
     * @param array $filters
     * @return array
     */
    public function getWarehouseStock(Warehouse $warehouse, array $filters = [])
    {
        $query = ProductWarehouseStock::where('warehouse_id', $warehouse->id)
            ->with('product');
        
        // Apply filters
        if (isset($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }
        
        if (isset($filters['category_id'])) {
            $query->whereHas('product.categories', function ($q) use ($filters) {
                $q->where('category_id', $filters['category_id']);
            });
        }
        
        if (isset($filters['low_stock'])) {
            $query->whereRaw('available_quantity < min_stock');
        }
        
        if (isset($filters['zero_stock'])) {
            $query->where('available_quantity', 0);
        }
        
        // Get the results
        $stockItems = $query->get();
        
        // Prepare the result
        $stockInfo = [
            'warehouse' => $warehouse,
            'total_products' => $stockItems->count(),
            'total_quantity' => $stockItems->sum('available_quantity'),
            'total_value' => 0,
            'items' => []
        ];
        
        foreach ($stockItems as $item) {
            $value = $item->available_quantity * $item->cmup;
            $stockInfo['total_value'] += $value;
            
            $stockInfo['items'][] = [
                'product' => $item->product,
                'available_quantity' => $item->available_quantity,
                'reserved_quantity' => $item->reserved_quantity,
                'total_quantity' => $item->getTotalQuantity(),
                'cmup' => $item->cmup,
                'value' => $value,
                'min_stock' => $item->min_stock,
                'max_stock' => $item->max_stock,
                'is_low_stock' => $item->isLowStock(),
                'is_over_stock' => $item->isOverStock()
            ];
        }
        
        return $stockInfo;
    }
    
    /**
     * Generate a reference number for stock transfers
     *
     * @return string
     */
    private function generateTransferReferenceNumber()
    {
        $prefix = 'TRF';
        $year = date('Y');
        $month = date('m');
        
        $lastTransfer = StockTransfer::where('reference_no', 'like', "{$prefix}{$year}{$month}%")
            ->orderBy('reference_no', 'desc')
            ->first();
        
        if ($lastTransfer) {
            $lastNumber = (int) substr($lastTransfer->reference_no, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . $year . $month . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
    
    /**
     * Generate a reference number for stock adjustments
     *
     * @return string
     */
    private function generateAdjustmentReferenceNumber()
    {
        $prefix = 'ADJ';
        $year = date('Y');
        $month = date('m');
        
        $lastAdjustment = StockAdjustment::where('reference_no', 'like', "{$prefix}{$year}{$month}%")
            ->orderBy('reference_no', 'desc')
            ->first();
        
        if ($lastAdjustment) {
            $lastNumber = (int) substr($lastAdjustment->reference_no, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . $year . $month . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}