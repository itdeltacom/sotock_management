<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
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
    public function salesOrders()
    {
        return $this->hasMany(SalesOrder::class);
    }

    public function stockDeliveries()
    {
        return $this->hasMany(StockDelivery::class);
    }

    // Helper method to get total sales amount
    public function getTotalSales($startDate = null, $endDate = null)
    {
        $query = $this->salesOrders()
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