<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'contact_person',
        'email',
        'phone',
        'address',
        'city',
        'country',
        'tax_id',
        'active',
        'notes',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    // Relationships
    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function stockReceptions()
    {
        return $this->hasMany(StockReception::class);
    }

    // Helper method to get total purchase amount
    public function getTotalPurchases($startDate = null, $endDate = null)
    {
        $query = $this->purchaseOrders()
            ->where('status', '!=', 'cancelled');
        
        if ($startDate) {
            $query->where('order_date', '>=', $startDate);
        }
        
        if ($endDate) {
            $query->where('order_date', '<=', $endDate);
        }
        
        return $query->sum('total_amount');
    }
}