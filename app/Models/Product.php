<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'description',
        'unit',
        'barcode',
        'sku',
        'active',
        'image',
        'attributes',
        'brand_id', // Add this line
    ];

    protected $casts = [
        'active' => 'boolean',
        'attributes' => 'array',
    ];

    // Relationships
    public function brand()
    {
        return $this->belongsTo(ProductBrand::class);
    }

    public function categories()
    {
        return $this->belongsToMany(ProductCategory::class, 'product_category', 'product_id', 'category_id');
    }

    public function stock()
    {
        return $this->hasMany(ProductWarehouseStock::class);
    }

    public function stockPackages()
    {
        return $this->hasMany(StockPackage::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function purchaseOrderItems()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function salesOrderItems()
    {
        return $this->hasMany(SalesOrderItem::class);
    }

    // Helpers
    public function getStockInWarehouse($warehouseId)
    {
        return $this->stock()
            ->where('warehouse_id', $warehouseId)
            ->first();
    }

    public function getTotalStock()
    {
        return $this->stock()
            ->sum('available_quantity');
    }

    public function getCMUP($warehouseId = null)
    {
        if ($warehouseId) {
            $stock = $this->getStockInWarehouse($warehouseId);
            return $stock ? $stock->cmup : 0;
        }

        // Calculate global CMUP across all warehouses
        $totalValue = 0;
        $totalQuantity = 0;

        foreach ($this->stock as $stock) {
            $totalValue += $stock->cmup * $stock->available_quantity;
            $totalQuantity += $stock->available_quantity;
        }

        return $totalQuantity > 0 ? $totalValue / $totalQuantity : 0;
    }
}