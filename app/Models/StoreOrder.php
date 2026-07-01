<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreOrder extends Model
{
    protected $fillable = [
        'user_id',
        'store_catalog_item_id',
        'voucher_id',
        'voucher_code',
        'voucher_name',
        'selected_format',
        'order_number',
        'customer_name',
        'customer_phone',
        'customer_email',
        'quantity',
        'unit_price',
        'subtotal_before_discount',
        'subtotal',
        'voucher_discount_amount',
        'shipping_address',
        'shipping_destination_province_id',
        'shipping_destination_province_name',
        'shipping_destination_city_id',
        'shipping_destination_city_name',
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
        'reader_access_token_hash',
        'reader_access_token_expires_at',
        'reader_last_device_hash',
        'reader_last_session_id',
        'reader_active_sessions',
        'reader_last_used_at',
        'admin_notes',
        'confirmed_at',
        'completed_at',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'subtotal_before_discount' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'voucher_discount_amount' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'gateway_expires_at' => 'datetime',
        'paid_at' => 'datetime',
        'shipped_at' => 'datetime',
        'reader_access_granted_at' => 'datetime',
        'reader_access_token_expires_at' => 'datetime',
        'reader_active_sessions' => 'integer',
        'reader_last_used_at' => 'datetime',
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

    public function voucher()
    {
        return $this->belongsTo(StoreVoucher::class, 'voucher_id');
    }
}
