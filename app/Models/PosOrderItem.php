<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PosOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'pos_order_id',
        'item_type',
        'publishing_package_id',
        'product_source_type',
        'product_source_id',
        'item_name',
        'item_description',
        'quantity',
        'unit_price',
        'discount_type',
        'discount_input',
        'discount_amount',
        'line_total_before_discount',
        'line_total',
        'extra_service_amount',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'discount_input' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'line_total_before_discount' => 'decimal:2',
        'line_total' => 'decimal:2',
        'extra_service_amount' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(PosOrder::class, 'pos_order_id');
    }

    public function publishingPackage()
    {
        return $this->belongsTo(PublishingPackage::class, 'publishing_package_id');
    }
}
