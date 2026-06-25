<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreOrder extends Model
{
    protected $fillable = [
        'user_id',
        'store_catalog_item_id',
        'order_number',
        'customer_name',
        'customer_phone',
        'customer_email',
        'quantity',
        'unit_price',
        'subtotal',
        'shipping_address',
        'shipping_destination_city_id',
        'shipping_service',
        'shipping_cost',
        'shipping_etd',
        'notes',
        'status',
        'payment_method',
        'payment_gateway',
        'payment_reference',
        'gateway_reference',
        'gateway_checkout_url',
        'gateway_expires_at',
        'paid_at',
        'tracking_number',
        'shipping_courier',
        'shipped_at',
        'reader_password_hash',
        'reader_access_granted_at',
        'admin_notes',
        'confirmed_at',
        'completed_at',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'gateway_expires_at' => 'datetime',
        'paid_at' => 'datetime',
        'shipped_at' => 'datetime',
        'reader_access_granted_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function item()
    {
        return $this->belongsTo(StoreCatalogItem::class, 'store_catalog_item_id');
    }
}
