<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreOrder extends Model
{
    protected $fillable = [
        'store_catalog_item_id',
        'order_number',
        'customer_name',
        'customer_phone',
        'customer_email',
        'quantity',
        'unit_price',
        'subtotal',
        'shipping_address',
        'notes',
        'status',
        'admin_notes',
        'confirmed_at',
        'completed_at',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'confirmed_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function item()
    {
        return $this->belongsTo(StoreCatalogItem::class, 'store_catalog_item_id');
    }
}
