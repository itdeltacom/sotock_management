<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'location',
        'description',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    // Relationships
    public function productStock()
    {
        return $this->hasMany(ProductWarehouseStock::class);
    }

    public function stockPackages()
    {
        return $this->hasMany(StockPackage::class);
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function salesOrders()
    {
        return $this->hasMany(SalesOrder::class);
    }

    public function stockReceptions()
    {
        return $this->hasMany(StockReception::class);
    }

    public function stockDeliveries()
    {
        return $this->hasMany(StockDelivery::class);
    }

    public function sourceTransfers()
    {
        return $this->hasMany(StockTransfer::class, 'source_warehouse_id');
    }

    public function destinationTransfers()
    {
        return $this->hasMany(StockTransfer::class, 'destination_warehouse_id');
    }

    public function stockAdjustments()
    {
        return $this->hasMany(StockAdjustment::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    // Helper to get stock of a specific product
    public function getProductStock($productId)
    {
        return $this->productStock()
            ->where('product_id', $productId)
            ->first();
    }
}